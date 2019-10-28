@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Загрузка данных</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @else

                    <form action="{{ route('feed_from_file') }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('file') ? ' has-error' : '' }}">
                            <label for="file" class="control-label">.XLSX файл</label>
                            <br>
                            <input id="file" type="file" name="file" required>

                            @if ($errors->has('file'))
                                <span class="help-block">
                                <strong class="text-danger">{{ $errors->first('file') }}</strong>
                                </span>
                            @endif

                        </div>

                        <p><button type="submit" class="btn btn-success" name="submit"><i class="fa fa-check"></i> Загрузить</button></p>

                    </form>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
