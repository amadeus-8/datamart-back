@extends('layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Добавить сотрудника</h1>
            </div>

        </div>
    </div><!-- /.container-fluid -->
</section>
<section class="content">
    <div class="container-fliud">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary" >

                    <form method ="POST" action = "{{ URL::to('users') }}" autocomplete="off">
                        @csrf
                        <div class="card-body" id = "addEmployee" >

                            <div class="form-group">
                                <label for="inputName">Имя</label>
                                <input type="text" class="form-control" id="inputName" name = "name">
                            </div>

                            <div class="form-group">
                                <label for="inputName">Логин(email)</label>
                                <input type="text" class="form-control" name = "email">
                            </div>

                            <div class="form-group">
                                <label for="inputPassword">Пароль</label>
                                <input type="password" class="form-control" name = "password">
                            </div>

                            <div class="form-group">
                                <label for="inputPasswordConfirm"> Подтверждение пароля</label>
                                <input type="password" class="form-control" name = "password_confirmation">
                            </div>

                            <div class="form-group">
                                <label>Роль</label>
                                @if(!$roles->isEmpty())
                                    <select name = "role" class="form-control" data-placeholder="Select a State" style="width: 100%;" tabindex="-1" aria-hidden="true">
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}" >{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Добавить</button>
                            @include('errors.list')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
