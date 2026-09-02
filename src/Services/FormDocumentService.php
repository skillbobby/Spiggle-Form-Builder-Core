<?php

namespace Spiggle\FormBuilder\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\PathResolver;
use Spiggle\FormBuilder\Support\SchemaNormalizer;

class FormDocumentService
{
    /**
     * @return array<string, mixed>
     */
    public function export(Form $form): array
    {
        $payload = SchemaNormalizer::document($form->toArray());

        $payload['description'] = (string) $form->description;
        $payload['settings'] = SchemaNormalizer::normalizePageChrome($form->settings ?? []);
        $payload['success_message'] = $form->success_message;
        $payload['redirect_url'] = $form->redirect_url;
        $payload['exported_at'] = now()->toIso8601String();

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    public function import(array $document, ?string $name = null): Form
    {
        if (! isset($document['schema']) || ! is_array($document['schema'])) {
            throw new InvalidArgumentException('The JSON file is missing a valid form schema.');
        }

        $importName = $name ?: (string) ($document['name'] ?? 'Imported form');
        $baseName = Str::endsWith($importName, ' (Imported)')
            ? $importName
            : $importName.' (Imported)';

        return Form::query()->create([
            'name' => $baseName,
            'slug' => Str::slug($baseName),
            'base_path' => PathResolver::suggest($baseName),
            'description' => $document['description'] ?? null,
            'container_type' => ContainerTypes::resolve((string) ($document['container_type'] ?? 'single')),
            'schema' => SchemaNormalizer::normalize($document['schema']),
            'settings' => SchemaNormalizer::normalizePageChrome(
                is_array($document['settings'] ?? null) ? $document['settings'] : []
            ),
            'success_message' => $document['success_message'] ?? 'Thanks — your response has been recorded.',
            'redirect_url' => $document['redirect_url'] ?? null,
            'is_published' => false,
            'is_active' => true,
        ]);
    }

    public function encode(Form $form): string
    {
        return json_encode($this->export($form), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function decode(string $json): array
    {
        $document = json_decode($json, true);

        if (! is_array($document)) {
            throw new InvalidArgumentException('The file does not contain valid JSON.');
        }

        return $document;
    }

    /**
     * Import one or more form documents from decoded JSON.
     *
     * @return list<Form>
     */
    public function importDocuments(array $document): array
    {
        if (isset($document['schema']) && is_array($document['schema'])) {
            return [$this->import($document)];
        }

        $imported = [];

        foreach ($document as $item) {
            if (! is_array($item) || ! isset($item['schema'])) {
                continue;
            }

            $imported[] = $this->import($item);
        }

        if ($imported === []) {
            throw new InvalidArgumentException('The JSON file is missing a valid form schema.');
        }

        return $imported;
    }

    public function importFromJson(string $json, ?string $name = null): Form
    {
        $document = $this->decode($json);
        $forms = $this->importDocuments($document);

        if ($name !== null && $name !== '') {
            $forms[0]->update(['name' => $name]);
        }

        return $forms[0];
    }
}
