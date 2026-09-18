@php
$company_settings = \App\Models\Utility::settings();
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
<style>
    .flatpickr-calendar {
        z-index: 999999 !important;
    }
</style>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
<script>
    var laravelFormat = "{{ $company_settings['site_date_format'] }}";
</script>

<script>
    (function() {
        var altFmt = laravelFormat || 'd-m-Y';

        var placeholder = altFmt
            .replace(/Y/g, 'YYYY')
            .replace(/y/g, 'YY')
            .replace(/F/g, 'Month')
            .replace(/M/g, 'MMM')
            .replace(/D/g, 'Day')
            .replace(/m/g, 'MM')
            .replace(/n/g, 'M')
            .replace(/d/g, 'DD')
            .replace(/j/g, 'D');

        function initOne(el) {
            if (!el || el._flatpickr || el.classList.contains('flatpickr-input')) return;
            if (el.dataset.noFlatpickr !== undefined) return;

            var existingValue = el.value && el.value.trim();
            var defaultDate = null;
            if (existingValue) {
                var attrValue = el.getAttribute('value');
                if (attrValue && attrValue.trim()) {
                    defaultDate = attrValue.trim();
                }
            }

            if (!defaultDate) {
                el.value = '';
            }

            flatpickr(el, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: altFmt,
                allowInput: false,
                disableMobile: true,
                appendTo: document.body,
                defaultDate: defaultDate || null,
                onReady: function(selectedDates, dateStr, instance) {
                    if (instance.altInput) {
                        instance.altInput.placeholder = placeholder;
                        instance.altInput.className = el.className.replace(/\b(datepicker|d_week)\b/g, '').trim() + ' flatpickr-input';

                        // Open picker when clicking anywhere on the alt input
                        instance.altInput.addEventListener('click', function() {
                            instance.open();
                        });

                        // Wrap the alt input in an input-group and append a calendar icon
                        var altInput = instance.altInput;
                        var parent = altInput.parentNode;

                        // Avoid double-wrapping
                        if (parent && !parent.classList.contains('flatpickr-input-group')) {
                            var wrapper = document.createElement('div');
                            wrapper.className = 'input-group flatpickr-input-group';
                            wrapper.style.flexWrap = 'nowrap';

                            parent.insertBefore(wrapper, altInput);
                            wrapper.appendChild(altInput);

                            var iconSpan = document.createElement('span');
                            iconSpan.className = 'input-group-text';
                            iconSpan.style.cursor = 'pointer';
                            iconSpan.innerHTML = '<i class="ti ti-calendar"></i>';
                            iconSpan.addEventListener('click', function() {
                                instance.open();
                            });
                            wrapper.appendChild(iconSpan);
                        }
                    }
                },
                onChange: function(selectedDates, dateStr) {
                    el.value = dateStr;
                    el.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            });
        }

        var dateSelector = 'input[type="date"]:not(.flatpickr-input), input.datepicker:not(.flatpickr-input), input.d_week:not(.flatpickr-input)';

        function initAll(root) {
            (root || document).querySelectorAll(dateSelector).forEach(initOne);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initAll();
            });
        } else {
            initAll();
        }

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) return;
                    if (node.classList.contains('flatpickr-input') || node.classList.contains('flatpickr-input-group')) return;

                    if (node.matches && node.matches(dateSelector)) {
                        initOne(node);
                    }
                    if (node.querySelectorAll) {
                        node.querySelectorAll(dateSelector).forEach(initOne);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        function displayDocumentIcon(filename, previewIcon, previewText) {

            const extension = filename.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                previewIcon.style.display = 'none';
                previewText.textContent = '';
                return;
            }

            let iconHTML = '';

            if (['xls', 'xlsx'].includes(extension)) {
                iconHTML = `
        <svg width="24" height="28">
            <rect x="2" y="2" width="20" height="24" rx="3" fill="#217346"/>
            <text x="50%" y="18" text-anchor="middle" font-size="10" fill="#fff">XLS</text>
        </svg>`;
            } else if (extension === 'pdf') {
                iconHTML = `
        <svg width="24" height="28">
            <rect x="2" y="2" width="20" height="24" rx="3" fill="#E74C3C"/>
            <text x="50%" y="18" text-anchor="middle" font-size="10" fill="#fff">PDF</text>
        </svg>`;
            } else if (['doc', 'docx'].includes(extension)) {
                iconHTML = `
        <svg width="24" height="28">
            <rect x="2" y="2" width="20" height="24" rx="3" fill="#2B579A"/>
            <text x="50%" y="18" text-anchor="middle" font-size="10" fill="#fff">DOC</text>
        </svg>`;
            } else if (['zip', 'rar'].includes(extension)) {
                iconHTML = `
        <svg width="24" height="28">
            <rect x="2" y="2" width="20" height="24" rx="3" fill="#F39C12"/>
            <text x="50%" y="18" text-anchor="middle" font-size="10" fill="#fff">ZIP</text>
        </svg>`;
            } else if (extension === 'txt') {
                iconHTML = `<span style="font-size:22px;">📄</span>`;
            } else {
                iconHTML = `<span style="font-size:22px;">📁</span>`;
            }

            previewIcon.innerHTML = iconHTML;
            previewIcon.style.display = 'inline-block';
            previewText.textContent = filename;
        }

        document.querySelectorAll('.documentDiv').forEach(function(input) {

            input.addEventListener('change', function(event) {

                const file = event.target.files[0];

                const container = input.closest('.form-group');

                const previewImg = container.querySelector('img');
                const previewText = container.querySelector('.documentPreview');
                const previewIcon = container.querySelector('.documentPreviewIcon');

                if (!file) {
                    previewImg.style.display = 'none';
                    previewText.textContent = '';
                    previewIcon.style.display = 'none';
                    return;
                }

                // Show file name
                previewText.textContent = file.name;

                displayDocumentIcon(file.name, previewIcon, previewText);
                // Show image preview
                if (file.type.startsWith('image/')) {
                    previewImg.src = URL.createObjectURL(file);
                    previewImg.style.display = 'block';
                } else {
                    previewImg.style.display = 'none';
                }

                // Show icon for non-image
                previewIcon.style.display = 'inline-block';

            });

        });
    })();
</script>
