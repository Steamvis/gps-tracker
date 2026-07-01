<tbody>
@foreach($cars as $car)
    <tr class="@if($car->is_connected_map) bg-success  @else bg-danger @endif text-white"
        data-car-id="{{ $car->id }}" style="cursor: pointer">
        <td class="text-center">
            <img src="{{ $car->image }}" alt="car-image" width="150px" height="100px">
        </td>
        <td>{{ $car->name }}</td>
        <td>{{ $car->brand->name }}</td>
        <td>{{ $car->api_code }}</td>
        <td>{{ $car->gov_number }}</td>
        <td>{{ $car->vin_number }}</td>
        <td>{{ $car->year }}</td>
        <td style="height: 1%; background-color: {{ $car->color }}"></td>
        <td>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button"
                        id="dropdownItemActions" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    {{ __('dashboard.general.forms.actions') }}
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownItemActions">
                    <form method="POST"
                          action="{{ route('cars.destroy', [
                                        'locale' => app()->getLocale(),
                                        'car' => $car,
                                    ]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                name="id"
                                onclick="callConfirmModal(this)"
                                data-title="{{ __('dashboard.general.forms.confirm') }}"
                                data-cancel="{{ __('dashboard.general.forms.cancel') }}"
                                data-confirm="{{ __('dashboard.general.forms.yes') }}"
                                class="dropdown-item" style="font-size: 14px">
                            {{ __('dashboard.general.CRUD.delete') }}
                        </button>
                    </form>
                    <a class="dropdown-item" style="font-size: 14px"
                       href="{{ route('cars.edit', [
                            'locale' => app()->getLocale(),
                            'car' => $car,
                        ]) }}">
                        {{ __('dashboard.general.CRUD.edit') }}
                    </a>
                    <a class="dropdown-item" style="font-size: 14px"
                       href="{{ route('cars.show', [
                            'locale' => app()->getLocale(),
                            'car' => $car,
                        ]) }}">
                        {{ __('dashboard.general.CRUD.view') }}
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
</tbody>

@section('pagination')
    <div class="card-footer">
        <div class="d-flex justify-content-between">
            @isset($search)
                {{ $cars->appends(['search' => $search])->links('dashboard.pagination') }}
            @else
                {{ $cars->links('dashboard.pagination') }}
            @endisset
        </div>
    </div>
@endsection

