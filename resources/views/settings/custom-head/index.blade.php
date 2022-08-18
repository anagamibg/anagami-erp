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
        .custom-head-hint {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')

    <div class="w-100 d-flex">

        @include('sections.setting-sidebar')

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
                            &bull; Тук можеш да слагаш произволен HTML код, който се инжектира в
                            <code>&lt;head&gt;</code> на всички страници. Удобно за:
                            <strong>Google Analytics, Facebook Pixel, meta tags, preload връзки,
                                third-party скриптове</strong>.
                        </li>
                        <li class="py-1">
                            &bull; Кодът се добавя <strong>преди</strong> Custom CSS блока,
                            така че CSS правилата от „Персонализиран CSS" страницата ще имат
                            предимство пред стилове, които сложиш тук.
                        </li>
                        <li class="py-1">
                            &bull; ⚠️ <strong>Внимание</strong>: невалиден HTML може да счупи цялата
                            страница. Тествай първо на тестова среда или прави промените на стъпки.
                        </li>
                        <li class="py-1">
                            &bull; След запис направи <em>hard refresh</em> (Cmd/Ctrl + Shift + R).
                        </li>
                    </ul>
                </x-alert>

                <div class="form-group">
                    <label class="font-weight-medium f-14 text-dark-grey mb-2" for="custom_head_html">
                        HTML за &lt;head&gt;
                    </label>
                    <textarea name="custom_head_html"
                              id="custom_head_html"
                              class="form-control"
                              rows="20">{{ $setting->custom_head_html }}</textarea>
                    <div class="custom-head-hint mt-2">
                        Например: <code>&lt;script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXX"&gt;&lt;/script&gt;</code>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closetag.min.js"></script>

    <script>
        window.headEditor = null;
        const CUSTOM_HEAD_SAVE_URL = "{{ route('custom-head.update') }}";

        $(document).on('click', '#save-form', function (e) {
            e.preventDefault();
            console.log('[custom-head] Save clicked');

            try {
                if (window.headEditor && typeof window.headEditor.save === 'function') {
                    window.headEditor.save();
                }
            } catch (err) {
                console.warn('[custom-head] headEditor.save() failed:', err);
            }

            const data = $('#editSettings').serialize();
            console.log('[custom-head] POST to:', CUSTOM_HEAD_SAVE_URL);

            $.easyAjax({
                url: CUSTOM_HEAD_SAVE_URL,
                container: '#editSettings',
                type: 'POST',
                data: data,
                blockUI: true,
                disableButton: true,
                buttonSelector: '#save-form',
                success: function (response) {
                    console.log('[custom-head] Save success:', response);
                },
                error: function (xhr) {
                    console.error('[custom-head] Save error:', xhr.status);
                }
            });
        });

        $(function () {
            if (typeof CodeMirror === 'undefined') {
                console.warn('[custom-head] CodeMirror not loaded — using plain textarea.');
                $('#custom_head_html').css({
                    'font-family': 'ui-monospace, SFMono-Regular, Menlo, monospace',
                    'font-size': '13px',
                    'min-height': '420px',
                    'tab-size': '2'
                });
                return;
            }

            try {
                window.headEditor = CodeMirror.fromTextArea(document.getElementById('custom_head_html'), {
                    mode: 'htmlmixed',
                    lineNumbers: true,
                    tabSize: 2,
                    indentUnit: 2,
                    indentWithTabs: false,
                    autoCloseBrackets: true,
                    autoCloseTags: true,
                    matchBrackets: true,
                    lineWrapping: true,
                    extraKeys: {
                        'Cmd-S': function () { $('#save-form').click(); return false; },
                        'Ctrl-S': function () { $('#save-form').click(); return false; }
                    }
                });
                console.log('[custom-head] CodeMirror initialized.');
            } catch (err) {
                console.error('[custom-head] CodeMirror init failed:', err);
            }
        });
    </script>
@endpush
