@extends('layouts.app')

@section('content')

    <div class="content-wrapper px-lg-5 px-3 py-4">

        <div class="d-flex align-items-center mb-4">
            <h2 class="mb-0 f-21 font-weight-normal text-capitalize">
                За приложението
            </h2>
        </div>

        <div class="row">

            <div class="col-lg-8">

                {{-- License --}}
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                 style="width:42px;height:42px;background:#eef2ff;">
                                <i class="fa fa-key text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">Лиценз на приложението</h5>
                                <p class="mb-0 text-muted f-13">
                                    Издаден от <strong>{{ $about['license_issuer'] }}</strong>
                                </p>
                            </div>
                        </div>
                        <div class="bg-light border rounded p-3 d-flex align-items-center justify-content-between flex-wrap">
                            <code id="appLicenseKey" class="f-15 text-dark mb-0">{{ $about['license'] }}</code>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary mt-2 mt-md-0"
                                    onclick="copyLicenseKey()">
                                <i class="fa fa-copy mr-1"></i> Копирай
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Application info + system details (merged) --}}
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-3">Информация за приложението</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Име на приложението</p>
                                <p class="mb-0 font-weight-medium">{{ $about['name'] }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Описание</p>
                                <p class="mb-0 font-weight-medium">{{ $about['tagline'] }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">URL на приложението</p>
                                <p class="mb-0 font-weight-medium">
                                    <a href="{{ $about['url'] }}" target="_blank" class="text-primary">{{ $about['url'] }}</a>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Версия на приложението</p>
                                <p class="mb-0 font-weight-medium">v{{ $version }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">PHP версия</p>
                                <p class="mb-0 font-weight-medium">
                                    {{ $phpVersion }}
                                    @if (version_compare(PHP_VERSION, '8.2.0') >= 0)
                                        <i class="fa fa-check-circle text-success ml-1"></i>
                                    @else
                                        <i class="fa fa-warning text-danger ml-1"
                                           data-toggle="tooltip" title="@lang('messages.phpUpdateRequired')"></i>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Laravel версия</p>
                                <p class="mb-0 font-weight-medium">{{ $laravelVersion }}</p>
                            </div>
                            @if (!is_null($dbVersion))
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <p class="mb-1 text-muted f-13">{{ $dbType }} версия</p>
                                    <p class="mb-0 font-weight-medium">{{ $dbVersion }}</p>
                                </div>
                            @endif
                            <div class="col-md-6 mb-0">
                                <p class="mb-1 text-muted f-13">Време на сървъра</p>
                                <p class="mb-0 font-weight-medium">{{ $serverTime->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Author --}}
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-3">Автор</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Име на компанията</p>
                                <p class="mb-0 font-weight-medium">{{ $about['author']['name'] }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Уебсайт</p>
                                <p class="mb-0 font-weight-medium">
                                    <a href="{{ $about['author']['website'] }}" target="_blank" class="text-primary">{{ $about['author']['website'] }}</a>
                                </p>
                            </div>
                            <div class="col-md-12 mb-0">
                                <p class="mb-1 text-muted f-13">Имейл</p>
                                <p class="mb-0 font-weight-medium">
                                    <a href="mailto:{{ $about['author']['email'] }}" class="text-primary">{{ $about['author']['email'] }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Owner --}}
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-3">Собственик</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Име на компанията</p>
                                <p class="mb-0 font-weight-medium">{{ $about['owner']['name'] }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1 text-muted f-13">Уебсайт</p>
                                <p class="mb-0 font-weight-medium">
                                    <a href="{{ $about['owner']['website'] }}" target="_blank" class="text-primary">{{ $about['owner']['website'] }}</a>
                                </p>
                            </div>
                            <div class="col-md-12 mb-0">
                                <p class="mb-1 text-muted f-13">Имейл</p>
                                <p class="mb-0 font-weight-medium">
                                    <a href="mailto:{{ $about['owner']['email'] }}" class="text-primary">{{ $about['owner']['email'] }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        function copyLicenseKey() {
            const text = document.getElementById('appLicenseKey').innerText.trim();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
        }
    </script>
@endpush
