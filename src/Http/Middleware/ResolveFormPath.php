<?php

namespace Spiggle\FormBuilder\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spiggle\FormBuilder\Models\Form;
use Symfony\Component\HttpFoundation\Response;

class ResolveFormPath
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('form-builder.root_paths', false)) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        if ($path === '') {
            return $next($request);
        }

        $reserved = config('form-builder.reserved_paths', []);
        $first = explode('/', $path)[0];
        if (in_array($first, $reserved, true)) {
            return $next($request);
        }

        $form = Form::query()->published()
            ->where('base_path', $path)
            ->orWhere('slug', $path)
            ->first();

        if (! $form) {
            return $next($request);
        }

        return app()->call(\Spiggle\FormBuilder\Livewire\PublicForm::class, ['path' => $path]);
    }
}
