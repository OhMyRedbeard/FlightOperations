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
        $airports = Airport::orderBy('icao')->get();
        $aircraft = Aircraft::orderBy('name')->get();
        $subfleets = Subfleet::orderBy('id')->get();
        $flightTypes = ['J' => 'Passenger', 'F' => 'Cargo'];
        $bids = Bid::orderBy('id')->where('user_id', Auth::id())->get();

        return view('flightoperations::index', compact('aircraft', 'airlines', 'airports', 'flightTypes', 'bids'));
        }

    public function createFlight(Request $request)
        {
        $request->merge(['flight_type' => (int) $request->flight_type]);

        $request->validate([
            'airline_id' => 'required|exists:airlines,id|max:1',
            'flight_number' => 'required|string|max:6',
            'flight_type' => 'required|integer|max:1',
            'dpt_airport_id' => 'required|exists:airports,id|max:4',
            'arr_airport_id' => 'required|exists:airports,id|different:dpt_airport_id|max:4',

        ]);

        try {
            $dptAirport = Airport::find($request->dpt_airport_id);
            $arrAirport = Airport::find($request->arr_airport_id);

            $distance = $this->calculateDistance(
                $dptAirport->lat,
                $dptAirport->lon,
                $arrAirport->lat,
                $arrAirport->lon,
            );

            $flight = new Flight();
            $flight->airline_id = $request->airline_id;
            $flight->flight_number = $request->flight_number;
            $flight->flight_type = $request->flight_type;
            $flight->dpt_airport_id = $request->dpt_airport_id;
            $flight->arr_airport_id = $request->arr_airport_id;
            $flight->distance = round($distance);
            $flight->save();

            $bid = new Bid();
            $bid->user_id = Auth::id();
            $bid->flight_id = $flight->id;
            $bid->save();

            $airlineCode = Airline::find($request->airline_id);
            $flightNumber = $airlineCode->icao . $flight->flight_number;

            return redirect()->route('flightoperations.index')
                ->with('success', $flightNumber . ' successfully created and added to your bids!');
            } catch (\Exception $e) {
            Log::error('Error creating flight: ' . $e->getMessage());
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
    
        return view('flightoperations::table', compact('aircraft'))->render();
        }
    public function generateFlightNumber()
        {
        $num = rand(1, 5000);
        return response()->json(['flight_number' => $num]);
        }
    public function deleteBid(Request $request, $bidId)
        {
        \Log::info('deleteBid called', $request->all());
        //return response()->json(['ok' => true]);
        $bid = Bid::where('id', $bidId);
        $bid->delete();
        return redirect()->back();
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