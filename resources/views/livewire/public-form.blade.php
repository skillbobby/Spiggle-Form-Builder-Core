@php
    $containers = $formModel->schema ?? [];
    $type = $this->resolvedLayout();
    $total = max(1, count($containers));
    $progress = (int) round((($step + 1) / $total) * 100);
@endphp

<div>
    @if ($submitted)
        <div class="fb-card fb-success" role="status">
            <h1 class="fb-title">{{ $formModel->name }}</h1>
            <p>{{ $successMessage }}</p>
            @if ($redirectUrl)
                <p><a href="{{ $redirectUrl }}">Continue</a></p>
            @endif
        </div>
    @else
        <form class="fb-card" wire:submit="submit" novalidate>
            <h1 class="fb-title">{{ $formModel->name }}</h1>
            @if ($formModel->description)
                <p class="fb-desc">{{ $formModel->description }}</p>
            @endif

            @if (in_array($type, ['wizard', 'pages'], true))
                <div class="fb-progress" aria-hidden="true"><span style="width: {{ $progress }}%"></span></div>
                <p class="fb-hint" id="fb-step-status">Step {{ $step + 1 }} of {{ $total }}: {{ $containers[$step]['label'] ?? 'Details' }}</p>
            @endif

            @if ($type === 'tabs')
                <div class="fb-pills" role="tablist" aria-label="{{ $formModel->name }} sections">
                    @foreach ($containers as $i => $container)
                        <button type="button" class="fb-pill" role="tab"
                            wire:click="goToStep({{ $i }})"
                            aria-selected="{{ $step === $i ? 'true' : 'false' }}">
                            {{ $container['label'] ?? 'Tab '.($i + 1) }}
                        </button>
                    @endforeach
                </div>
            @endif

            @foreach ($containers as $i => $container)
                @php $visible = $type === 'single' || $i === $step; @endphp
                <section class="{{ $visible ? '' : 'fb-hidden' }}"
                    @if ($type === 'tabs') role="tabpanel" @endif
                    aria-label="{{ $container['label'] ?? 'Section' }}">
                    @if ($type !== 'single' && ! empty($container['description']))
                        <p class="fb-desc">{{ $container['description'] }}</p>
                    @endif
                    <div class="fb-grid">
                        @foreach ($container['fields'] ?? [] as $field)
                            @include('form-builder::components.field', ['field' => $field, 'formModel' => $formModel])
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="fb-actions">
                @if (in_array($type, ['wizard', 'pages'], true) && $step > 0)
                    <button type="button" class="fb-btn secondary" wire:click="previousStep">Back</button>
                @endif

                @if (in_array($type, ['wizard', 'pages'], true) && $step < $total - 1)
                    <button type="button" class="fb-btn" wire:click="nextStep">
                        {{ $type === 'pages' ? 'Save and continue' : 'Next' }}
                    </button>
                @else
                    <button type="submit" class="fb-btn" wire:loading.attr="disabled">Submit</button>
                @endif
            </div>
        </form>
    @endif
</div>
