@extends('layouts.app')

@push('styles')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.css">
    <style>
        .CodeMirror {
            height: 520px;
            font-size: 13px;
            line-height: 1.5;
            border: 1px solid #e9ecef;
            border-radius: 6px;
        }
        .custom-css-hint {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        @include('sections.setting-sidebar')

        {{-- Pass method="POST" so the auto-generated form posts to our POST route (no PUT spoofing) --}}
        <x-setting-card method="POST">

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                        @lang($pageTitle)
                    </h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">

                <x-alert type="secondary">
                    <ul class="mb-0">
                        <li class="py-1">
                            &bull; Тук можеш да слагаш свои CSS правила. Те се прилагат на <strong>всички страници</strong>
                            и имат <strong>най-висок приоритет</strong> — инжектират се най-накрая в <code>&lt;head&gt;</code>.
                        </li>
                        <li class="py-1">
                            &bull; За override на нещо конкретно използвай по-специфичен селектор или
                            <code>!important</code>.
                        </li>
                        <li class="py-1">
                            &bull; Промените се прилагат веднага след запис — направи <em>hard refresh</em>
                            (Cmd/Ctrl + Shift + R) ако не виждаш ефект.
                        </li>
                    </ul>
                </x-alert>

                <div class="form-group">
                    <label class="font-weight-medium f-14 text-dark-grey mb-2" for="custom_css">
                        CSS код
                    </label>
                    <textarea name="custom_css"
                              id="custom_css"
                              class="form-control"
                              rows="20">{{ $setting->custom_css }}</textarea>
                    <div class="custom-css-hint mt-2">
                        Например: <code>.sidebar { background: #1a1a1a !important; }</code>
                    </div>
                </div>

            </div>

            <x-slot name="action">
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-form" class="mr-3" icon="check">
                            @lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
            </x-slot>

        </x-setting-card>

    </div>

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/css-hint.min.js"></script>

    <script>
        window.cssEditor = null;
        const CUSTOM_CSS_SAVE_URL = "{{ route('custom-css.update') }}";

        // Bind click via delegation so it works even if CodeMirror fails to init
        $(document).on('click', '#save-form', function (e) {
            e.preventDefault();
            console.log('[custom-css] Save clicked');

            // Sync editor → textarea
            try {
                if (window.cssEditor && typeof window.cssEditor.save === 'function') {
                    window.cssEditor.save();
                }
            } catch (err) {
                console.warn('[custom-css] cssEditor.save() failed:', err);
            }

            // The whole settings page is wrapped in <form id="editSettings"> by x-setting-card.
            // Grab its serialized data; that includes _token + our textarea.
            const data = $('#editSettings').serialize();
            console.log('[custom-css] POST to:', CUSTOM_CSS_SAVE_URL);
            console.log('[custom-css] Payload length:', data.length, 'bytes');

            $.easyAjax({
                url: CUSTOM_CSS_SAVE_URL,
                container: '#editSettings',
                type: 'POST',
                data: data,
                blockUI: true,
                disableButton: true,
                buttonSelector: '#save-form',
                success: function (response) {
                    console.log('[custom-css] Save success:', response);
                },
                error: function (xhr) {
                    console.error('[custom-css] Save error:', xhr.status);
                }
            });
        });

        // Init CodeMirror
        $(function () {
            if (typeof CodeMirror === 'undefined') {
                console.warn('[custom-css] CodeMirror not loaded — using plain textarea.');
                $('#custom_css').css({
                    'font-family': 'ui-monospace, SFMono-Regular, Menlo, monospace',
                    'font-size': '13px',
                    'min-height': '420px',
                    'tab-size': '2'
                });
                return;
            }

            try {
                window.cssEditor = CodeMirror.fromTextArea(document.getElementById('custom_css'), {
                    mode: 'css',
                    lineNumbers: true,
                    tabSize: 2,
                    indentUnit: 2,
                    indentWithTabs: false,
                    autoCloseBrackets: true,
                    matchBrackets: true,
                    lineWrapping: true,
                    extraKeys: {
                        'Ctrl-Space': 'autocomplete',
                        'Cmd-S': function () { $('#save-form').click(); return false; },
                        'Ctrl-S': function () { $('#save-form').click(); return false; }
                    }
                });
                console.log('[custom-css] CodeMirror initialized.');
            } catch (err) {
                console.error('[custom-css] CodeMirror init failed:', err);
            }
        });
    </script>
@endpush
