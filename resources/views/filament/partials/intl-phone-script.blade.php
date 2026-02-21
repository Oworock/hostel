<script>
    (function () {
        const COUNTRY_CODES = [
            ['NG', '+234'], ['US', '+1'], ['GB', '+44'], ['CA', '+1'], ['IN', '+91'], ['GH', '+233'],
            ['KE', '+254'], ['ZA', '+27'], ['AE', '+971'], ['SA', '+966'], ['FR', '+33'], ['DE', '+49'],
            ['IT', '+39'], ['ES', '+34'], ['BR', '+55'], ['AU', '+61'], ['JP', '+81'], ['CN', '+86'],
        ];

        function normalizeDial(value) {
            const digits = String(value || '').replace(/[^\d]/g, '');
            return digits ? '+' + digits : '';
        }

        function initField(input) {
            if (!(input instanceof HTMLInputElement) || input.dataset.phoneIntlEnhanced === '1') return;
            const fieldName = input.getAttribute('name') || '';
            if (!fieldName || fieldName.endsWith('_country_code')) return;

            input.dataset.phoneIntlEnhanced = '1';
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2';
            input.parentNode.insertBefore(row, input);
            row.appendChild(input);

            const select = document.createElement('select');
            select.className = 'fi-input block w-[120px] rounded-lg border-gray-300 text-sm';
            COUNTRY_CODES.forEach(([country, dial]) => {
                const option = document.createElement('option');
                option.value = dial;
                option.textContent = country + ' ' + dial;
                select.appendChild(option);
            });

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = fieldName + '_country_code';

            const parsedDial = (String(input.value || '').match(/^\+[\d]{1,4}/) || [])[0] || '';
            select.value = parsedDial || '+234';
            hidden.value = select.value;

            row.prepend(select);
            input.parentNode.appendChild(hidden);

            select.addEventListener('change', function () {
                hidden.value = normalizeDial(select.value) || '+234';
                const raw = String(input.value || '').trim();
                const stripped = raw.replace(/^\+\d{1,4}\s*/, '');
                input.value = (hidden.value + ' ' + stripped).trim();
            });
        }

        function scan(root) {
            const selector = 'input[type="tel"], input[name="phone"], input[name$="_phone"]';
            (root || document).querySelectorAll(selector).forEach(initField);
        }

        document.addEventListener('DOMContentLoaded', function () {
            scan(document);
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node instanceof HTMLElement) {
                            scan(node);
                        }
                    });
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    })();
</script>

