@extends('app')

@section('title', 'Flight Operations')

@section('content')
    @include('flightoperations::table', ['airlineId' => 2])
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Flight Operations</h2>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- Flight Creation Form -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Create New Flight</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('flightoperations.create-flight') }}">
                            @csrf

                            <!-- Airline Selection -->
                            <div class="form-group">
                                <label for="airline_id">Airline</label>
                                <select name="airline_id" id="airline_id" class="form-control" required>
                                    <option value="">Select Airline</option>
                                    @foreach($airlines as $airline)
                                        <option value="{{ $airline->id }}">
                                            {{ $airline->code }} - {{ $airline->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Aircraft Select -->
                            <div class="form-group">
                                <label for="aircraft_id">Aircraft</label>
                                <select name="aircraft_id" id="aircraft_id" class="form-control" required>
                                    <option selected>Please select an airline first.</option>
                                </select>
                            </div>
                            <!-- Flight Number with Auto-Generate -->
                            <div class="form-group">
                                <label for="flight_number">Flight Number</label>
                                <div class="input-group">
                                    <input type="text" name="flight_number" id="flight_number" class="form-control"
                                        value="{{ session('generated_flight_number') ?? session('duplicate_data.flight_number') ?? '' }}"
                                        required>
                                    <div class="input-group-append">
                                        <!-- Convert to form submission -->
                                        <button type="submit" name="action" value="generate_flight_number"
                                            class="btn btn-secondary">
                                            Generate Random
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Departure Airport -->
                            <div class="form-group">
                                <label for="dpt_airport_id">Departure Airport</label>
                                <select name="dpt_airport_id" id="dpt_airport_id" class="form-control" required>
                                    <option value="">Select Departure Airport</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}">
                                            {{ $airport->icao }} - {{ $airport->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Arrival Airport -->
                            <div class="form-group">
                                <label for="arr_airport_id">Arrival Airport</label>
                                <select name="arr_airport_id" id="arr_airport_id" class="form-control" required>
                                    <option value="">Select Arrival Airport</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}">
                                            {{ $airport->icao }} - {{ $airport->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Flight Type -->
                            <div class="form-group">
                                <label for="flight_type">Flight Type</label>
                                <select name="flight_type" id="flight_type" class="form-control" required>
                                    <option value="">Select Flight Type</option>
                                    <option value="J" {{ session('duplicate_data.flight_type') == 'J' ? 'selected' : '' }}>
                                        Passenger</option>
                                    <option value="C" {{ session('duplicate_data.flight_type') == 'C' ? 'selected' : '' }}>
                                        Cargo</option>
                                    <option value="O" {{ session('duplicate_data.flight_type') == 'O' ? 'selected' : '' }}>
                                        Charter</option>
                                    <option value="T" {{ session('duplicate_data.flight_type') == 'T' ? 'selected' : '' }}>
                                        Training</option>
                                    <option value="H" {{ session('duplicate_data.flight_type') == 'H' ? 'selected' : '' }}>
                                        Helicopter</option>
                                </select>
                            </div>

                            <button type="submit" name="action" value="create_flight" class="btn btn-primary">Create Flight
                                & Add to Bids</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- User Bids -->
            <div class="col-md-6">
                <div class="card">

                    <div class="card-body">
                        @if($bids->count() > 0)
                            @foreach($bids as $bid)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            {{ $bid->flight->airline->code }}{{ $bid->flight->flight_number }}
                                            <small class="text-muted">{{ $bid->flight->airline->name }}</small>
                                        </h6>
                                        <p class="card-text">
                                            <strong>Route:</strong> {{ $bid->flight->dpt_airport->icao }} →
                                            {{ $bid->flight->arr_airport->icao }}<br>
                                            <small class="text-muted">{{ $bid->flight->dpt_airport->name }} to
                                                {{ $bid->flight->arr_airport->name }}</small>
                                            <!--                                                                 <a href="#" class="testButton" data-bs-toggle="" data-bs-target="#testModal"
                                                                                                                                                                    data-id="{{ $bid->id }}"
                                                                                                                                                                    data-url="{{ route('flightoperations.delete-bid', ['bidId' => $bid->id]) }}">Click
                                                                                                                                                                    here</a> -->

                                            <a href="#" class="remove-bid" data-bs-toggle="modal" data-bs-target="#removeConfirm"
                                                data-id="{{ $bid->id }}"
                                                data-url="{{ route('flightoperations.delete-bid', ['bidId' => $bid->id]) }}"
                                                data-flight-number="{{ $bid->flight->airline->icao}}{{ $bid->flight->flight_number }}">Delete
                                                Bid</a>

                                        </p>

                                        <!-- Aircraft selection form -->

                                        <!--                                     data-url="{{ route('flightoperations.delete-bid', ['bidId' => $bid->id]) }}"
                                                                                                                                 -->
                                        <!-- Action buttons as forms -->
                                        <div class="btn-group-vertical btn-group-sm">



                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No flight bids found. Create a flight above to get started!</p>
                        @endif

                        <a href="#" class="testButton" data-bs-toggle="" data-bs-target="#testModal" data-id="1234"
                            data-url="https://google.com/">Click here</a>
                        <div class="modal" id="testModal">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="removeConfirm" tab-index="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header flex-column border-0">
                    <i style="font-size: 5rem;" class="material-symbols-outlined text-danger">cancel</i>
                    <h4 class="modal-title w-100">Are you sure?</h4>
                </div>
                <div class="modal-body text-center px-5">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="removeBtn">Yes, delete</a>
                        <button type="reset" class="btn btn-danger" id="cancelBtn" data-bs-dismiss="modal">No, don't
                            delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const removeBtn = document.getElementById('removeBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const removeBids = document.querySelectorAll('.remove-bid');

            removeBids.forEach(btn => {
                const id = btn.getAttribute('data-id');
                const url = btn.getAttribute('data-url');
                const flightNumber = btn.getAttribute('data-flight-number');
                btn.addEventListener('click', () => {
                    const target = document.querySelector("#removeConfirm .modal-dialog .modal-body");
                    removeBtn.setAttribute('data-id', id);
                    removeBtn.setAttribute('data-url', url);

                    target.innerHTML = "Are you sure you want to delete " + flightNumber + "? Once confirmed, this cannot be undone.";
                });
            });

            cancelBtn.addEventListener('click', () => {
                removeBtn.removeAttribute('data-id');
                removeBtn.removeAttribute('data-url');
            });

            removeBtn.addEventListener('click', () => {
                const id = removeBtn.getAttribute('data-id');
                const url = removeBtn.getAttribute('data-url');
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const api = document.querySelector('meta[name="api-key"]').getAttribute('content');

                const xhr = new XMLHttpRequest();
                xhr.onload = function () {
                    console.log("Response:", xhr.status, xhr.responseText);
                    if (xhr.status === 200) {
                        window.location.reload();
                    }
                };

                xhr.open("POST", url, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
                xhr.setRequestHeader("X-API-KEY", api);
                xhr.send(id);
            });

            document.getElementById('airline_id').addEventListener('change', function () {
                const airlineId = this.value;
                if (!airlineId) {
                    document.getElementById('aircraft_id').innerHTML = '';
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