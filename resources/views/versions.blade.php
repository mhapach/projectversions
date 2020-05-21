<?php
/**
 * @var \mhapach\ProjectVersions\Models\VcsLog[] $vcsLogs
 * @var \mhapach\ProjectVersions\Models\VcsLog $vcsLog
 */
?>
@extends('projectversions::layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h4 class="text-danger" id="new-message" style="display: none;">Внимание есть более свежая версия проекта!</h4>
                <div class="card">
                    <div class="card-header">
                        <span class="pr-3">
                            Текущая версия проекта: <span id="version-span">{!! app('version') !!} </span>
                        </span>

                        <span class="text-danger" id="last-version-message" style="display: none;">
                            Последняя версия проекта: <span id="last-version-span"></span>
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="form-group">
                            <label for="version">Список последних версий</label>
                            <select name="version_select" id="version-select" class="form-control">
                                <option value="0">
                                    Обновить до последней версии
                                </option>
                                @if (isset($vcsLogs))
                                    @foreach ($vcsLogs as $vcsLog)
                                        <option value="{!! $vcsLog->revision !!}">
                                            {!! $vcsLog->msg ?: $vcsLog->revision !!} -
                                            дата({{$vcsLog->date->format('d.m.Y H:i:s')}})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                    </div>

                    <div class="card-footer">
                        <div class="input-group">
                            <button class="btn btn-primary" id="checkoutButton">Установить выбранную версию</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {

            checkNewVersion();

            $('#checkoutButton').on("click", function () {
                let url = '{{route('project_version.checkout', ['revision' => 0])}}';

                if ($('#version-select').val())
                    url = url.replace(/\d+$/, $('#version-select').val());

                $.get(url)
                    .done(function (msg) {
                        alert('Версия успешно установлена');
                        $('#version-span').html(msg.version);
                        checkNewVersion();
                        console.log(msg); //
                    })
                    .fail(function (msg) {
                        alert('Произошла ошибка при установке');
                        console.log(msg);
                    });
            });

            function checkNewVersion() {
                url = '{{route('project_version.new')}}';

                $.get(url)
                    .done(function (msg) {
                        if (msg.hasOwnProperty('result') && msg.result) {
                            $('#last-version-span').html(msg.result);
                            $('#last-version-message').show();
                            $('#new-message').show();
                        }
                        else {
                            $('#last-version-message').hide();
                            $('#new-message').hide();
                        }
                    })
                    .fail(function (msg) {
                        alert('Произошла ошибка при поиске новой версии');
                        console.log(msg);
                    });
            }
        }, false);
    </script>
@endsection

