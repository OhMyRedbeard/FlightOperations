<?php

namespace Modules\FlightOperations\Services;

use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SimbriefService
{
    /**
     * Generate SimBrief URL for flight planning
     */
    public function generateFlightPlanUrl(Bid $bid, Aircraft $aircraft, User $user): string
    {
        $flight = $bid->flight;
        
        // Base SimBrief dispatch URL
        $baseUrl = 'https://www.simbrief.com/system/dispatch.php';
        
        // Build parameters for SimBrief
        $params = [
            // Airline information
            'airline' => $flight->airline->icao ?? $flight->airline->code,
            'fltnum' => $flight->flight_number,
            
            // Aircraft information
            'type' => $this->getAircraftType($aircraft),
            'reg' => $aircraft->registration,
            'fin' => $aircraft->fin_nbr ?? $aircraft->registration,
            'selcal' => $aircraft->selcal ?? '',
            
            // Route information
            'orig' => $flight->dpt_airport->icao,
            'dest' => $flight->arr_airport->icao,
            'route' => $flight->route ?? 'DCT', // Direct if no route specified
            
            // Pilot information
            'pilot' => $user->name_private ?? $user->name,
            'pid' => $user->pilot_id ?? $user->id,
            'cpt' => $user->name_private ?? $user->name,
            
            // Flight planning options
            'planformat' => 'LIDO', // Default format
            'units' => 'KGS', // Weight units
            'navlog' => 'DETAILED',
            'etops' => '1',
            'stepclimbs' => '1',
            'tlr' => '1', // Traffic load restrictions
            
            // Additional options
            'date' => date('dMy'), // Today's date
            'deph' => '12', // Default departure hour
            'depm' => '00', // Default departure minute
        ];
        
        // Add flight level if available
        if ($flight->level) {
            $params['fl'] = $flight->level;
        }
        
        // Add alternate airports if available
        if ($flight->alt_airport_id) {
            $params['altn'] = $flight->alt_airport->icao ?? '';
        }
        
        // Add cruise altitude based on aircraft performance
        $params['fl'] = $this->getCruiseAltitude($aircraft, $flight);
        
        // Add fuel planning
        $params['contpct'] = '5.0'; // 5% contingency fuel
        $params['resvrule'] = 'AUTO'; // Auto reserve fuel
        
        Log::info('Generated SimBrief URL parameters', $params);
        
        return $baseUrl . '?' . http_build_query($params);
    }
    
    /**
     * Get aircraft type for SimBrief
     */
    private function getAircraftType(Aircraft $aircraft): string
    {
        // Try to get ICAO type from aircraft
        if ($aircraft->icao) {
            return $aircraft->icao;
        }
        
        // Fall back to subfleet type
        if ($aircraft->subfleet && $aircraft->subfleet->type) {
            return $aircraft->subfleet->type;
        }
        
        // Default fallback
        return 'B738'; // Boeing 737-800 as default
    }
    
    /**
     * Get appropriate cruise altitude for aircraft and route
     */
    private function getCruiseAltitude(Aircraft $aircraft, $flight): string
    {
        // Default altitudes based on aircraft type
        $defaultAltitudes = [
            // Narrow body jets
            'B737' => 'FL370',
            'B738' => 'FL370',
            'A320' => 'FL370',
            'A321' => 'FL370',
            
            // Wide body jets
            'B777' => 'FL390',
            'B787' => 'FL410',
            'A330' => 'FL390',
            'A350' => 'FL410',
            
            // Regional jets
            'CRJ2' => 'FL350',
            'E145' => 'FL350',
            'E170' => 'FL370',
            
            // Turboprops
            'AT72' => 'FL250',
            'DH8D' => 'FL250',
        ];
        
        $aircraftType = $this->getAircraftType($aircraft);
        
        // Check for exact match
        if (isset($defaultAltitudes[$aircraftType])) {
            return $defaultAltitudes[$aircraftType];
        }
        
        // Check for partial matches
        foreach ($defaultAltitudes as $type => $altitude) {
            if (strpos($aircraftType, $type) !== false) {
                return $altitude;
            }
        }
        
        // Calculate based on distance if no match found
        $distance = $flight->distance ?? 500;
        
        if ($distance < 500) {
            return 'FL250'; // Short flights
        } elseif ($distance < 1500) {
            return 'FL350'; // Medium flights
        } else {
            return 'FL390'; // Long flights
        }
    }
    
    /**
     * Validate SimBrief parameters
     */
    public function validateParameters(Bid $bid, Aircraft $aircraft): array
    {
        $errors = [];
        
        $flight = $bid->flight;
        
        // Check required flight data
        if (!$flight->dpt_airport || !$flight->dpt_airport->icao) {
            $errors[] = 'Departure airport ICAO code missing';
        }
        
        if (!$flight->arr_airport || !$flight->arr_airport->icao) {
            $errors[] = 'Arrival airport ICAO code missing';
        }
        
        if (!$flight->airline) {
            $errors[] = 'Airline information missing';
        }
        
        // Check aircraft data
        if (!$aircraft->registration) {
            $errors[] = 'Aircraft registration missing';
        }
        
        if (!$this->getAircraftType($aircraft)) {
            $errors[] = 'Aircraft type information missing';
        }
        
        // Check subfleet assignment
        if (!$aircraft->subfleet) {
            $errors[] = 'Aircraft not assigned to subfleet';
        }
        
        return $errors;
    }
    
    /**
     * Get SimBrief flight plan status (if API key is available)
     */
    public function getFlightPlanStatus(string $flightId): ?array
    {
        // This would require SimBrief API integration
        // For now, return null as it requires API key setup
        return null;
    }
    
    /**
     * Generate enhanced SimBrief URL with weather and NOTAM data
     */
    public function generateEnhancedFlightPlanUrl(Bid $bid, Aircraft $aircraft, User $user, array $options = []): string
    {
        $baseUrl = $this->generateFlightPlanUrl($bid, $aircraft, $user);
        
        // Add enhanced options
        $enhancedParams = [];
        
        // Weather options
        if (isset($options['include_weather']) && $options['include_weather']) {
            $enhancedParams['wx'] = '1';
            $enhancedParams['wxsrc'] = 'NOAA'; // Weather source
        }
        
        // NOTAM options
        if (isset($options['include_notams']) && $options['include_notams']) {
            $enhancedParams['notams'] = '1';
        }
        
        // Performance options
        if (isset($options['detailed_performance']) && $options['detailed_performance']) {
            $enhancedParams['perfcalc'] = '1';
            $enhancedParams['climb'] = 'AUTO';
            $enhancedParams['descent'] = 'AUTO';
        }
        
        // Fuel planning options
        if (isset($options['fuel_planning'])) {
            $enhancedParams['contpct'] = $options['fuel_planning']['contingency'] ?? '5.0';
            $enhancedParams['resvrule'] = $options['fuel_planning']['reserve_rule'] ?? 'AUTO';
            $enhancedParams['taxifuel'] = $options['fuel_planning']['taxi_fuel'] ?? 'AUTO';
        }
        
        if (!empty($enhancedParams)) {
            $baseUrl .= '&' . http_build_query($enhancedParams);
        }
        
        return $baseUrl;
    }
}
