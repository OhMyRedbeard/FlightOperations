# Flight Operations Module for PHPVMS7

A comprehensive flight operations module that allows users to create flights and manage their bids with integrated SimBrief flight planning.

## Features

### Flight Creation
- Create new flights with automatic bid addition
- Airline selection (active airlines only)
- Random flight number generation (0-5000 range)
- Departure and arrival airport selection
- Flight type selection (Passenger, Cargo, Charter, Training, Helicopter)

### Bid Management
- View all user bids in a clean table format
- Aircraft selection dropdown filtered by airline
- Edit bid preferences
- Delete unwanted bids
- Duplicate existing flights for quick creation

### SimBrief Integration
- Basic SimBrief flight plan generation
- Enhanced SimBrief with weather, NOTAMs, and performance data
- Automatic aircraft type detection
- Intelligent cruise altitude calculation
- Fuel planning options (contingency, reserve rules, taxi fuel)

### Statistics
- Real-time flight statistics display
- Total bids count
- Flights created count
- Airlines used count
- Favorite airline identification

## Installation

1. Place the module in your PHPVMS `/modules` directory
2. Run the migration: `php artisan migrate`
3. The module will be automatically discovered and registered

## Usage

Navigate to `/flight-operations` to access the module interface.

### Creating a Flight
1. Select an airline from the dropdown
2. Generate a random flight number or enter manually
3. Choose departure and arrival airports
4. Select flight type
5. Submit to create flight and add to bids

### Managing Bids
1. Select aircraft for each bid
2. Use SimBrief integration for flight planning
3. Edit bid preferences as needed
4. Duplicate flights for similar routes

### SimBrief Options
- **Basic**: Quick flight plan generation
- **Enhanced**: Includes weather, NOTAMs, and detailed performance calculations

## Configuration

Edit `Config/config.php` to customize:
- Flight number generation range
- Default flight time and distance
- Module settings

## Requirements

- PHPVMS v7.x
- Active airlines in the system
- Aircraft assigned to subfleets
- Airports with ICAO codes

## API Endpoints

The module provides RESTful API endpoints for:
- Flight number generation
- Aircraft retrieval by airline
- Bid management (create, update, delete)
- SimBrief integration
- Flight statistics

## Logging

All flight operations are logged to the `flight_operations_log` table for audit purposes.

## Support

This module follows PHPVMS7 development standards and integrates seamlessly with existing functionality.
