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
        .custom-js-hint {
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
                            &bull; Тук можеш да слагаш свои JavaScript снипети. Те се изпълняват на
                            <strong>всички страници</strong> непосредствено преди <code>&lt;/body&gt;</code>
                            — след като jQuery и всички vendor скриптове са заредени.
                        </li>
                        <li class="py-1">
                            &bull; Кодът е обвит в <code>try/catch</code> — ако нещо гръмне, страницата
                            ще продължи да работи; грешката отива в browser console-а.
                        </li>
                        <li class="py-1">
                            &bull; Можеш да ползваш <code>$(document).on(...)</code>, <code>fetch()</code>,
                            <code>window.X</code>, всичко глобално достъпно.
                        </li>
                        <li class="py-1">
                            &bull; След запис направи <em>hard refresh</em> (Cmd/Ctrl + Shift + R)
                            за да заредиш новия код.
                        </li>
                    </ul>
                </x-alert>

                <div class="form-group">
                    <label class="font-weight-medium f-14 text-dark-grey mb-2" for="custom_js">
                        JavaScript код
                    </label>
                    <textarea name="custom_js"
                              id="custom_js"
                              class="form-control"
                              rows="20">{{ $setting->custom_js }}</textarea>
                    <div class="custom-js-hint mt-2">
                        Например: <code>$('body').on('click', '.my-button', function() { alert('hi'); });</code>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/javascript-hint.min.js"></script>

    <script>
        window.jsEditor = null;
        const CUSTOM_JS_SAVE_URL = "{{ route('custom-js.update') }}";

        $(document).on('click', '#save-form', function (e) {
            e.preventDefault();
            console.log('[custom-js] Save clicked');

            try {
                if (window.jsEditor && typeof window.jsEditor.save === 'function') {
                    window.jsEditor.save();
                }
            } catch (err) {
                console.warn('[custom-js] jsEditor.save() failed:', err);
            }

            const data = $('#editSettings').serialize();
            console.log('[custom-js] POST to:', CUSTOM_JS_SAVE_URL);

            $.easyAjax({
                url: CUSTOM_JS_SAVE_URL,
                container: '#editSettings',
                type: 'POST',
                data: data,
                blockUI: true,
                disableButton: true,
                buttonSelector: '#save-form',
                success: function (response) {
                    console.log('[custom-js] Save success:', response);
                },
                error: function (xhr) {
                    console.error('[custom-js] Save error:', xhr.status);
                }
            });
        });

        $(function () {
            if (typeof CodeMirror === 'undefined') {
                console.warn('[custom-js] CodeMirror not loaded — using plain textarea.');
                $('#custom_js').css({
                    'font-family': 'ui-monospace, SFMono-Regular, Menlo, monospace',
                    'font-size': '13px',
                    'min-height': '420px',
                    'tab-size': '2'
                });
                return;
            }

            try {
                window.jsEditor = CodeMirror.fromTextArea(document.getElementById('custom_js'), {
                    mode: 'javascript',
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
                console.log('[custom-js] CodeMirror initialized.');
            } catch (err) {
                console.error('[custom-js] CodeMirror init failed:', err);
            }
        });
    </script>
@endpush
