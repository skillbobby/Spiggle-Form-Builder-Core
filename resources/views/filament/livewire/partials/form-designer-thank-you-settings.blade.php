<section class="fd-thank-settings">
    <header class="fd-thank-settings-header">
        <h3>Thank you page</h3>
        <p class="fd-hint">Choose a layout, then click blocks in the preview to edit copy. Use the fields below for colors, icons, and CTA links.</p>
    </header>

    <div class="fd-thank-settings-grid">
        <label class="fd-field">
            <span>Redirect URL <span class="fd-field-optional">(optional)</span></span>
            <input type="url" class="fd-input" wire:model.blur="redirectUrl" placeholder="https://">
        </label>

        <div class="fd-thank-settings-toggles">
            @if ($thankYouLayout === 'card')
                <label class="fd-toggle">
                    <input type="checkbox" wire:model.live="thankYouShowFormName">
                    <span>Show form name</span>
                </label>
            @endif
            <label class="fd-toggle">
                <input type="checkbox" wire:model.live="thankYouShowTimestamp">
                <span>Show submission time</span>
            </label>
        </div>

        <label class="fd-field">
            <span>Layout</span>
            <select class="fd-input" wire:model.live="thankYouLayout">
                @foreach ($thankYouLayoutLabels as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>

        @if (! $proUnlocked)
            <p class="fd-hint">Upgrade to Pro for Review Card and Connect layouts plus premium content blocks.</p>
        @endif
    </div>

    @if ($thankYouLayout === 'review_card')
        <div class="fd-thank-layout-meta">
            <h4>Review Card settings</h4>
            <div class="fd-thank-settings-grid">
                <label class="fd-field">
                    <span>Page background</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('page_background', $event.target.value)" value="{{ $thankYouLayoutMeta['page_background'] ?? '#3b82f6' }}">
                </label>
                <label class="fd-field">
                    <span>Header band color</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('header_band_color', $event.target.value)" value="{{ $thankYouLayoutMeta['header_band_color'] ?? '#a5b4fc' }}">
                </label>
                <label class="fd-field">
                    <span>Hero icon type</span>
                    <select class="fd-input" wire:change="updateThankYouLayoutMeta('hero_icon_type', $event.target.value)">
                        <option value="emoji" @selected(($thankYouLayoutMeta['hero_icon_type'] ?? 'emoji') === 'emoji')>Emoji / icon</option>
                        <option value="image" @selected(($thankYouLayoutMeta['hero_icon_type'] ?? 'emoji') === 'image')>Image URL</option>
                    </select>
                </label>
                @if (($thankYouLayoutMeta['hero_icon_type'] ?? 'emoji') === 'image')
                    <label class="fd-field fd-field-span">
                        <span>Hero image URL</span>
                        <input type="url" class="fd-input" wire:model.blur="thankYouLayoutMeta.hero_image_url" wire:change="updateThankYouLayoutMeta('hero_image_url', $event.target.value)" placeholder="https://">
                    </label>
                @else
                    <label class="fd-field">
                        <span>Hero emoji / icon</span>
                        <input type="text" class="fd-input" wire:model.blur="thankYouLayoutMeta.hero_icon" wire:change="updateThankYouLayoutMeta('hero_icon', $event.target.value)" maxlength="8">
                    </label>
                @endif
                <label class="fd-field">
                    <span>CTA button label</span>
                    <input type="text" class="fd-input" wire:model.blur="thankYouLayoutMeta.cta_label" wire:change="updateThankYouLayoutMeta('cta_label', $event.target.value)">
                </label>
                <label class="fd-field">
                    <span>CTA button URL</span>
                    <input type="url" class="fd-input" wire:model.blur="thankYouLayoutMeta.cta_url" wire:change="updateThankYouLayoutMeta('cta_url', $event.target.value)" placeholder="https://">
                </label>
                <label class="fd-field">
                    <span>CTA button color</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('cta_color', $event.target.value)" value="{{ $thankYouLayoutMeta['cta_color'] ?? '#2563eb' }}">
                </label>
            </div>
        </div>
    @endif

    @if ($thankYouLayout === 'campaign')
        <div class="fd-thank-layout-meta">
            <h4>Connect layout settings</h4>
            <div class="fd-thank-settings-grid">
                <label class="fd-field">
                    <span>Page background</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('page_background', $event.target.value)" value="{{ $thankYouLayoutMeta['page_background'] ?? '#f8fafc' }}">
                </label>
                <label class="fd-field">
                    <span>Accent color (top-left)</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('accent_colors.0', $event.target.value)" value="{{ $thankYouLayoutMeta['accent_colors'][0] ?? '#f5b800' }}">
                </label>
                <label class="fd-field">
                    <span>Accent color (bottom-right)</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('accent_colors.1', $event.target.value)" value="{{ $thankYouLayoutMeta['accent_colors'][1] ?? '#10b981' }}">
                </label>
                <label class="fd-field">
                    <span>Success icon</span>
                    <input type="text" class="fd-input" wire:model.blur="thankYouLayoutMeta.success_icon" wire:change="updateThankYouLayoutMeta('success_icon', $event.target.value)" maxlength="4">
                </label>
                <label class="fd-field">
                    <span>Success icon color</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('success_icon_color', $event.target.value)" value="{{ $thankYouLayoutMeta['success_icon_color'] ?? '#10b981' }}">
                </label>
            </div>

            <div class="fd-thank-settings-grid">
                <label class="fd-field">
                    <span>Connect card title</span>
                    <input type="text" class="fd-input" wire:model.blur="thankYouLayoutMeta.connect_card_title" wire:change="updateThankYouLayoutMeta('connect_card_title', $event.target.value)">
                </label>
                <label class="fd-field">
                    <span>Website card title</span>
                    <input type="text" class="fd-input" wire:model.blur="thankYouLayoutMeta.website_card_title" wire:change="updateThankYouLayoutMeta('website_card_title', $event.target.value)">
                </label>
                <label class="fd-field">
                    <span>Website button label</span>
                    <input type="text" class="fd-input" wire:model.blur="thankYouLayoutMeta.website_button_label" wire:change="updateThankYouLayoutMeta('website_button_label', $event.target.value)">
                </label>
                <label class="fd-field">
                    <span>Website button URL</span>
                    <input type="url" class="fd-input" wire:model.blur="thankYouLayoutMeta.website_button_url" wire:change="updateThankYouLayoutMeta('website_button_url', $event.target.value)" placeholder="https://">
                </label>
                <label class="fd-field">
                    <span>Website button color</span>
                    <input type="color" class="fd-input fd-color-input" wire:change="updateThankYouLayoutMeta('website_button_color', $event.target.value)" value="{{ $thankYouLayoutMeta['website_button_color'] ?? '#10b981' }}">
                </label>
            </div>

            <div class="fd-thank-social-links">
                <div class="fd-thank-social-links-head">
                    <h5>Social links</h5>
                    <button type="button" class="fd-btn-ghost" wire:click="addThankYouSocialLink">+ Add link</button>
                </div>
                @foreach ($thankYouLayoutMeta['social_links'] ?? [] as $index => $link)
                    <div class="fd-thank-social-link-row" wire:key="thank-you-social-{{ $index }}">
                        <select class="fd-input" wire:change="updateThankYouSocialLink({{ $index }}, 'platform', $event.target.value)">
                            @foreach (['facebook', 'linkedin', 'pinterest', 'twitter', 'instagram', 'youtube', 'tiktok'] as $platform)
                                <option value="{{ $platform }}" @selected(($link['platform'] ?? '') === $platform)>{{ ucfirst($platform) }}</option>
                            @endforeach
                        </select>
                        <input type="url" class="fd-input" placeholder="https://" value="{{ $link['url'] ?? '' }}" wire:change="updateThankYouSocialLink({{ $index }}, 'url', $event.target.value)">
                        <button type="button" class="fd-btn-ghost fd-btn-danger" wire:click="removeThankYouSocialLink({{ $index }})" aria-label="Remove social link">&times;</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
