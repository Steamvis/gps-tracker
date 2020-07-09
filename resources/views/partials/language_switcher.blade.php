@foreach($locales as $locale)
    @empty(Route::current()->token)
        @php($data = array_merge(['token' => Route::current()->token], Route::current()->parameters))
    @endempty
    @php($data = Route::current()->parameters)
    <a href="{{ route(Route::currentRouteName(), $data) }}"
       class="dropdown-item p-0 text-center py-2">{{ Str::upper($locale) }}</a>
@endforeach
