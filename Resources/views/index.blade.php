@extends('app')

@if(Auth::user()->email_verified_at === NULL)
    {!! redirect()->to('/dashboard')->send() !!}
@endif

@section('title', 'Flight Operations')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            {{-- <h4>Flight Operations</h4>
            <div>Book your next passenger or charter flight, or view your current bids here!</div>--}}
            <span class="fw-bold">@lang('vingar.flightoperations')</span> - @lang('vingar.booknext')
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h5>@lang('vingar.createnew')</h5>
                    <form method="POST" action="{{ route('flightoperations.create-flight') }}">
                        @csrf

                        {{-- Airline Selection --}}
                        <div class="form-group mb-3">
                            <label for="airline_id" class="mb-1">@lang('vingar.airline')</label>
                            <select name="airline_id" id="airline_id" class="form-select" required>
                                <option value="">@lang('vingar.selectairline')</option>
                                @foreach($airlines as $airline)
                                    <option value="{{ $airline->id }}">{{ $airline->code }} - {{ $airline->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Aircraft Selection --}}
                        <div class="form-group mb-3">
                            <label for="aircraft_id" class="mb-1">@lang('vingar.aircraft')</label>
                            <select name="aircraft_id" id="aircraft_id" class="form-select" required>
                                <option value="" selected>@lang('vingar.pleaseselectairline')</option>
                            </select>
                        </div>

                        {{-- Flight Number with Auto-Generate --}}
                        <div class="form-group mb-3">
                            <label for="flight_number" class="mb-1">@lang('vingar.flightnumber')</label>
                            <div class="input-group">
                                <input type="text" name="flight_number" id="flight_number" class="form-control" required>
                                <button type="button" name="generate_flight_number" id="generate_flight_number"
                                    class="btn btn-secondary">@lang('vingar.generate')</button>

                            </div>
                        </div>

                        {{-- Departure Airport Selection --}}
                        <div class="form-group mb-3">
                            <label for="dpt_airport_id" class="mb-1">@lang('vingar.origin')</label>
                            <select name="dpt_airport_id" id="dpt_airport_id" class="form-select">
                                <option value="">@lang('vingar.selectorigin')</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" {{ old('dpt_airport_id') == $airport->id ? 'selected' : '' }}>{{ $airport->icao }} - {{ $airport->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Arrival Airport Selection --}}
                        <div class="form-group mb-3">
                            <label for="arr_airport_id" class="mb-1">@lang('vingar.destination')</label>
                            <select name="arr_airport_id" id="arr_airport_id" class="form-select">
                                <option value="" selected>@lang('vingar.selectdestination')</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" {{ old('arr_airport_id') == $airport->id ? 'selected' : '' }}>{{ $airport->icao }} - {{ $airport->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Flight Type Selection --}}
                        <div class="form-group mb-3">
                            <label for="flight_type">@lang('vingar.flightype')</label>
                            <select name="flight_type" id="flight_type" class="form-select" required>
                                <option value="" selected>@lang('vingar.selecttype')</option>
                                @foreach($flightTypes as $key => $name)
                                    <option value="{{ $key }}" {{ old('flight_type') == $key ? 'selected' : '' }}>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" name="action" value="create_flight"
                            class="btn btn-secondary">@lang('vingar.createflight')</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h5>@lang('vingar.viewbids')</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Flight Number</th>
                                <th scope="col">Departure</th>
                                <th scope="col">Arrival</th>
                                <th scope="col">Distance</th>
                                <th scope="col">Created</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bids as $bid)
                                <tr>
                                    <td>{{ $bid->flight->airline->icao }}{{ $bid->flight->flight_number }}
                                    </td>
                                    <td>{{ $bid->flight->dpt_airport->icao }}</td>
                                    <td>{{ $bid->flight->arr_airport->icao }}</td>
                                    <td>{{ $bid->flight->distance }}nm</td>
                                    <td>{{ Carbon::parse($bid->created_at)->diffForHumans() }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                class="dropdown-toggle">Actions</a>
                                            <div class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                                <div class="dropdown-item">
                                                    <div class="mb-1">
                                                        <button class="btn btn-sm btn-danger remove-bid w-100"
                                                            data-id="{{ $bid->id }}"
                                                            data-url="{{ route('flightoperations.delete-bid', ['bidId' => $bid->id]) }}"
                                                            data-flight-number="{{ $bid->flight->airline->icao}}{{ $bid->flight->flight_number }}"
                                                            data-bs-target="#remove_bid_modal" data-bs-toggle="modal">Remove
                                                            Bid</button>
                                                    </div>
                                                    <div class="mb-1">
                                                        @if(!empty(setting('simbrief.api_key')))
                                                            @if($bid->flight->simbrief && $bid->flight->simbrief->user_id === Auth::id())
                                                                <a href="{{ route('frontend.simbrief.briefing', $bid->flight->simbrief->id) }}"
                                                                    class="btn btn-sm btn-secondary w-100">View SimBrief Flight Plan</a>
                                                            @else
                                                                <a href="{{ route('frontend.simbrief.generate') }}?flight_id={{ $bid->flight->id }}&aircraft_id={{ $bid->aircraft_id }}"
                                                                    class="btn btn-sm btn-secondary w-100">Plan with SimBrief</a>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <div class="mb-1">
                                                        <a href="vmsacars:bid/{{$bid->id}}">
                                                            <button class="btn btn-sm btn-primary w-100">Send to
                                                                VMSACARS</button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="remove_bid_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <span class="mdi mdi-close-circle-outline display-1 text-danger"></span>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button id="remove_confirm" class="btn btn-success" data-bs-dismiss="modal">Yes, delete.</button>
                    <button id="remove_cancel" class="btn btn-danger" data-bs-dismiss="modal">No, don't delete.</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function generateFlightNumber() {
                const x = document.getElementById('flight_number');

                x.setAttribute('value', Math.floor(Math.random() * 5000) + 1);
            }

            generateFlightNumber();

            document.getElementById('generate_flight_number').addEventListener('click', () => {
                generateFlightNumber();
            });

            const removeBid = document.querySelectorAll('.remove-bid');
            const removeBidModal = document.querySelector('#remove_bid_modal .modal-body');
            const removeConfirm = document.getElementById('remove_confirm');
            const removeCancel = document.getElementById('remove_cancel');

            removeBid.forEach(btn => {
                const id = btn.getAttribute('data-id');
                const url = btn.getAttribute('data-url');
                const flightNumber = btn.getAttribute('data-flight-number');

                btn.addEventListener('click', () => {
                    removeConfirm.setAttribute('data-id', id);
                    removeConfirm.setAttribute('data-url', url);

                    removeBidModal.innerHTML = "Are you sure you want to delete " + flightNumber + "? Once confirmed, this cannot be undone.";
                });
            });

            removeConfirm.addEventListener('click', () => {
                const bidId = removeConfirm.getAttribute('data-id');
                const url = removeConfirm.getAttribute('data-url');
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const apiKey = document.querySelector('meta[name="api-key"]').getAttribute('content');

                const xhr = new XMLHttpRequest();

                xhr.onload = function () {
                    console.log("Response: ", xhr.status, xhr.responseText);
                    if (xhr.status === 200) {
                        window.location.reload();
                    }
                }

                xhr.open("POST", url, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
                xhr.setRequestHeader('X-API-KEY', apiKey);
                xhr.send(bidId);
            });

            document.getElementById('airline_id').addEventListener('change', function () {
                const airlineId = this.value;
                if (!airlineId || this.value === '') {
                    document.getElementById('aircraft_id').innerHTML = '<option value="" selected>Please Select An Airline First</option>';
                    return;
                }

                fetch(`{{ url('flightoperations/get-fleet') }}/${airlineId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('aircraft_id').innerHTML = html;
                    })
                    .catch(err => console.error(err));
            });
        });
    </script>
@endsection