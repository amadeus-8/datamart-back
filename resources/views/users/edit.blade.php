@extends('layouts.app')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Редактировать пользователя</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fliud" id = "app">

            <div class='col-lg-4 col-lg-offset-4' id="app">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div><br />
                @endif
                <form method="post" action="{{ route('users.update', $user->id) }}">
                    @method('PATCH')
                    @csrf
                    <div class="form-group">
                        <label for="name">Имя</label>
                        <input type="text" class="form-control" name="name" value={{ $user->name }} />
                    </div>
                    <div class="form-group">
                        <label for="email">Логин(Email)</label>
                        <input type="text" class="form-control" name="email" value={{ $user->email }} />
                    </div>
                    <div class="form-group">
                        <label for="role">Роль</label>
                        <select class="form-control" name="role" value={{ $user->role }}>
                            @foreach($roles as $role)
                                <option value={{$role->id}}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Новый пароль</label>
                        <input type="password" class="form-control" name="password" />
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Повторите новый пароль</label>
                        <input type="password" class="form-control" name="password_confirmation" />
                    </div>

                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>

            </div>
        </div>
    </section>

@endsection
