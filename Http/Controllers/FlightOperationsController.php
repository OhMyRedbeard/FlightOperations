<?php

namespace Modules\FlightOperations\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Bid;
use App\Models\Flight;
use App\Models\Subfleet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FlightOperationsController extends Controller
    {
    public function index()
        {
        $airlines = Airline::orderBy('id')->where('active', true)->get();
        $aircraft = Aircraft::orderBy('name')->get();
        $subfleets = Subfleet::orderBy('id')->get();
        $airports = Airport::orderBy('icao')->get();
        $flightTypes = [
            'J' => 'Passenger',
            'F' => 'Cargo',
        ];
        $bids = Bid::orderBy('id')->where('user_id', Auth::id())->get();

        return view('flightoperations::index', compact('airlines', 'aircraft', 'subfleets', 'airports', 'flightTypes', 'bids'));

        }
    public function createFlight(Request $request)
        {
        $request->validate([
            'airline_id' => 'required|exists:airlines,id',
            'aircraft_id' => 'required|exists:aircraft,id',
            'flight_number' => 'required|string|max:6',
            'flight_type' => 'required|string|in:J,F',
            'dpt_airport_id' => 'required|exists:airports,id',
            'arr_airport_id' => 'required|exists:airports,id',
        ]);

        try {
            $dptAirport = Airport::findOrFail($request->dpt_airport_id);
            $arrAirport = Airport::findOrFail($request->arr_airport_id);

            $distance = $this->calculateDistance(
                $dptAirport->lat,
                $dptAirport->lon,
                $arrAirport->lat,
                $arrAirport->lon
            );

            $flight = Flight::create([
                'airline_id' => $request->airline_id,
                'aircraft_id' => $request->aircraft_id,
                'flight_type' => $request->flight_type,
                'flight_number' => $request->flight_number,
                'route_code' => 'FFM',
                'dpt_airport_id' => $request->dpt_airport_id,
                'arr_airport_id' => $request->arr_airport_id,
                'distance' => round($distance),
            ]);

            $bid = Bid::create([
                'user_id' => Auth::id(),
                'flight_id' => $flight->id,
                'aircraft_id' => $request->aircraft_id,
            ]);

            $airlineCode = Airline::find($request->airline_id);
            $flightNumber = $airlineCode->icao . $flight->flight_number;

            return redirect()->route('flightoperations.index')
                ->with('success', $flightNumber . ' successfully created and added to your bids!');
            } catch (\Exception $e) {
            Log::error('Error creating Flight: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Error creating flight: ' . $e->getMessage())
                ->withInput();
            }
        }

    public function getFleet($airlineId)
        {
        $subfleet = Subfleet::where('airline_id', $airlineId)->pluck('id');
        $aircraft = Aircraft::whereIn('subfleet_id', $subfleet)->get();

        return view('flightoperations::aircraft_table', compact('aircraft'))->render();
        }

    public function generateFlightNumber()
        {
        $num = rand(1, 5000);
        return response()->json(['flight_number' => $num]);
        }

    public function deleteBid(Request $request, $bidId)
        {
        $bid = Bid::where('id', $bidId);

        try {
            $bid->delete();
            return redirect()->route('flightoperations::index')
                ->with('success', 'Bid successfully deleted!');
            } catch (\Exception $e) {
            Log::error('Error deleting bid: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Error deleting bid: ' . $e->getMessage())
                ->withInput();
            }
        }
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
        {
        $earthRadius = 3440.065;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
        }
    }
