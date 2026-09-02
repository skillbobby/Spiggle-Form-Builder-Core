(() => {
    const onlyDigits = (value) => String(value ?? '').replace(/\D/g, '');

    const formatPhone = (digits) => {
        if (!digits) {
            return '';
        }

        if (digits.length <= 3) {
            return `(${digits}`;
        }

        if (digits.length <= 6) {
            return `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
        }

        return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
    };

    const formatDate = (digits) => {
        if (!digits) {
            return '';
        }

        if (digits.length <= 2) {
            return digits;
        }

        if (digits.length <= 4) {
            return `${digits.slice(0, 2)}/${digits.slice(2)}`;
        }

        return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4, 8)}`;
    };

    const formatTime = (digits) => {
        if (!digits) {
            return '';
        }

        if (digits.length <= 2) {
            return digits;
        }

        return `${digits.slice(0, 2)}:${digits.slice(2, 4)}`;
    };

    const formatCurrency = (raw) => {
        const value = String(raw ?? '').replace(/[^\d.]/g, '');
        if (!value) {
            return '';
        }

        const parts = value.split('.');
        let whole = parts[0].replace(/^0+(?=\d)/, '') || '0';
        const fraction = parts.slice(1).join('').slice(0, 2);

        whole = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        if (value.includes('.')) {
            return `$${whole}.${fraction}`;
        }

        return `$${whole}`;
    };

    const formatters = {
        phone: (value) => formatPhone(onlyDigits(value).slice(0, 10)),
        date: (value) => formatDate(onlyDigits(value).slice(0, 8)),
        time: (value) => formatTime(onlyDigits(value).slice(0, 4)),
        currency: (value) => formatCurrency(value),
    };

    const caretAfterDigitIndex = (formatted, digitIndex) => {
        if (digitIndex <= 0) {
            return 0;
        }

        let seen = 0;
        for (let i = 0; i < formatted.length; i += 1) {
            if (/\d/.test(formatted[i])) {
                seen += 1;
                if (seen >= digitIndex) {
                    return i + 1;
                }
            }
        }

        return formatted.length;
    };

    const countDigitsBefore = (value, position) => {
        let count = 0;
        const limit = Math.max(0, Math.min(position ?? 0, value.length));

        for (let i = 0; i < limit; i += 1) {
            if (/\d/.test(value[i])) {
                count += 1;
            }
        }

        return count;
    };

    const applyMask = (maskType, value) => {
        const formatter = formatters[maskType];
        return formatter ? formatter(value) : String(value ?? '');
    };

    const register = () => {
        if (!window.Alpine) {
            return;
        }

        window.Alpine.data('fbInputMask', (maskType) => ({
            maskType,

            init() {
                this.$nextTick(() => {
                    const formatted = applyMask(this.maskType, this.$el.value);
                    if (formatted !== this.$el.value) {
                        this.$el.value = formatted;
                    }
                });
            },

            handleInput(event) {
                const input = event.target;
                const digitIndex = countDigitsBefore(input.value, input.selectionStart);
                const formatted = applyMask(this.maskType, input.value);

                input.value = formatted;

                const nextPos = caretAfterDigitIndex(formatted, digitIndex);
                try {
                    input.setSelectionRange(nextPos, nextPos);
                } catch (_) {
                    // Some mobile browsers reject caret placement on type=tel.
                }
            },
        }));
    };

    document.addEventListener('alpine:init', register);

    if (window.Alpine) {
        register();
    }
})();
