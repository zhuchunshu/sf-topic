<div class="row row-cards">

    <div class="col-md-10">
        <div class="card">

            <div class="card-status-top" style="{{Core_Ui()->Css()->bg_color($data->color)}}"></div>

            <div class="card-body">
                <h3 class="card-title">
                    {{$data->name}}
                </h3>
                <p>
                    {{core_default($data->description,get_options("description","无描述"))}}
                </p>
            </div>

            <div class="card-footer">
                @if(auth()->check())
                    <a href="/topic/create" class="btn btn-dark">发帖</a>
                @else
                    <a href="/login" class="btn btn-dark">登陆</a>
                    <a href="/register" class="btn btn-light">注册</a>
                @endif
            </div>

        </div>
    </div>

    @foreach(Itf()->get("index_right") as $value)
        @include($value)
    @endforeach
</div>