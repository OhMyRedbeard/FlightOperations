@extends('app')

@section('title', 'Flight Operations')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4>Flight Operations</h4>
            <div>Book your next passenger or charter flight, or view your current bids here!</div>
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
                    <h5>Create new flight</h5>
                    <form method="POST" action="{{ route('flightoperations.create-flight') }}">
                        @csrf

                        {{-- Airline Selection --}}
                        <div class="form-group mb-3">
                            <label for="airline_id" class="mb-1">Airline</label>
                            <select name="airline_id" id="airline_id" class="form-control" required>
                                <option value="">Select Airline</option>
                                @foreach($airlines as $airline)
                                    <option value="{{ $airline->id }}">{{ $airline->code }} - {{ $airline->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Aircraft Selection --}}
                        <div class="form-group mb-3">
                            <label for="aircraft_id" class="mb-1">Aircraft</label>
                            <select name="aircraft_id" id="aircraft_id" class="form-control" required>
                                <option value="" selected>Please Select An Airline First</option>
                            </select>
                        </div>

                        {{-- Flight Number with Auto-Generate --}}
                        <div class="form-group mb-3">
                            <label for="flight_number" class="mb-1">Flight Number</label>
                            <div class="input-group input-group-append">
                                <input type="text" name="flight_number" id="flight_number" class="form-control" required>
                                    <button type="button" name="generate_flight_number" id="generate_flight_number"
                                        class="btn btn-secondary border-left-0">Generate</button>

                            </div>
                        </div>

                        {{-- Departure Airport Selection --}}
                        <div class="form-group mb-3">
                            <label for="dpt_airport_id" class="mb-1">Departure Airport</label>
                            <select name="dpt_airport_id" id="dpt_airport_id" class="form-control">
                                <option value="">Select Departure Airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" {{ old('dpt_airport_id') == $airport->id ? 'selected' : '' }}>{{ $airport->icao }} - {{ $airport->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Arrival Airport Selection --}}
                        <div class="form-group mb-3">
                            <label for="arr_airport_id" class="mb-1">Arrival Airport</label>
                            <select name="arr_airport_id" id="arr_airport_id" class="form-control">
                                <option value="" selected>Select Arrival Airport</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" {{ old('arr_airport_id') == $airport->id ? 'selected' : '' }}>{{ $airport->icao }} - {{ $airport->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Flight Type Selection --}}
                        <div class="form-group mb-3">
                            <label for="flight_type">Flight Type</label>
                            <select name="flight_type" id="flight_type" class="form-control" required>
                                <option value="" selected>Select Flight Type</option>
                                @foreach($flightTypes as $key => $name)
                                    <option value="{{ $key }}" {{ old('flight_type') == $key ? 'selected' : '' }}>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" name="action" value="create_flight" class="btn btn-secondary">Create Flight
                            &amp; Add to Bids</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card">

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /*             const removeBtn = document.getElementById('removeBtn');
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
                        }); */

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