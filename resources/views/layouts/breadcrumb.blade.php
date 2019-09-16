<nav>
    <ol class="breadcrumb">
        @foreach ($pageArr as $item)
            <li class="breadcrumb-item">
                <a href="/{{ $item['href'] }}">{{ trans('title.' . $item['title']) }}</a>
            </li>            
        @endforeach
        <li class="breadcrumb-item">{{ trans('title.' . $cur) }}</li>
    </ol>
</nav>