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
                            {{__('Current version of project')}}: <span id="version-span">{!! app('project.version') !!} </span>
                        </span>

                        <span class="text-danger" id="last-version-message" style="display: none;">
                            {{__('Last version of project')}}: <span id="last-version-span"></span>
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="form-group">
                            <label for="version">{{__('List of versions')}}</label>
                            <select name="version_select" id="version-select" class="form-control">
                                <option value="0">
                                    {{__('Checkout last version')}}
                                </option>
                                @if (isset($vcsLogs))
                                    @foreach ($vcsLogs as $vcsLog)
                                        <option value="{!! $vcsLog->revision !!}">
                                            {!! $vcsLog->msg ?: $vcsLog->revision !!} -
                                            {{__('date')}} ({{$vcsLog->date->format('d.m.Y H:i:s')}})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                    </div>

                    <div class="card-footer">
                        <div class="input-group">
                            <button class="btn btn-primary" id="checkoutButton">{{__('Checkout chosen version')}}</button>
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
                let url = '{{route('project_versions.checkout', ['revision' => 0])}}';

                if ($('#version-select').val())
                    url = url.replace(/\d+$/, $('#version-select').val());

                $.get(url)
                    .done(function (msg) {
                        if (msg.hasOwnProperty('result') && msg.result) {
                            alert('{{__('Version successfully installed')}}');
                            $('#version-span').html(msg.version);
                            checkNewVersion();
                        }
                        if (msg.hasOwnProperty('result') && !msg.result) {
                            alert(msg.message);
                        }
                        console.log(msg); //
                    })
                    .fail(function (msg) {
                        alert('{{__('Checkout error')}}');
                        console.log(msg);
                    });
            });

            function checkNewVersion() {
                url = '{{route('project_versions.new')}}';

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
                        alert('{{__('Error of getting new version')}}');
                        console.log(msg);
                    });
            }
        }, false);
    </script>
@endsection

