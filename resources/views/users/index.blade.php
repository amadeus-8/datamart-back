@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Список пользователей</h1>
                </div>
                <div class="col-sm-6">
                    <ul class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ URL::to('users/create') }}"  type = "button" class = "btn btn-outline-primary">Добавить</a></li>
                    </ul>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Имя</th>
                                    <th>Логин(email)</th>
                                    <th>Дата добавления</th>
                                    <th>Роль</th>
                                </tr>
                                @foreach ($users as $user)
                                    <tr>

                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ date('d.m.Y', strtotime($user->created_at)) }}</td>
                                        <td>{{ $user->roles()->pluck('name')->implode(' ') }}</td>
                                        <td>
                                            <a href="{{ route('users.edit', $user->id) }}">Редактировать</a>
                                            {{--|--}}
                                            {{--<a href="#"--}}
                                               {{--onclick="event.preventDefault();--}}
                                                     {{--document.getElementById('destroy-form').submit();">--}}
                                                {{--Удалить--}}
                                            {{--</a>--}}

                                            {{--<form id="destroy-form" action="{{ route('users.destroy', $user->id) }}" method="DELETE" style="display: none;">--}}
                                                {{--@csrf--}}
                                            {{--</form>--}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>

            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
@endsection
