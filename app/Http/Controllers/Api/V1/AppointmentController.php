<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentClientCollection;
use App\Http\Resources\AppointmentClientResource;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $user)
    {
        $date = $request->input('filter.date');
        $appointment = Appointment::select([
            'appointments.*',
            'services.title',
            'services.category',
            'masters.firstname as master_firstname',
            'masters.lastname as master_lastname',
            'schedules.date',
            'schedules.time',
        ])
            ->join('schedules', 'appointments.schedule_id', '=', 'schedules.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->where('appointments.client_id', '=', $user)
            ->when($date, function ($query) use ($date) {
                $query->whereIn('date', $date);
            })
            ->get();
//        $appointment = Appointment::with([
//            'schedule' => function ($query) {
//                $query->with('master');
//            },
//            'service'
//        ])
//            ->where('client_id', $user)
//            ->when($date, function ($query) use ($date) {
//                $query->whereIn('date', $date);
//            })
//            ->get();
//        dd($appointment->toArray());
        if (!empty($appointment->toArray())) {
            $appointmentCollection = new AppointmentClientCollection($appointment);
            return response()->json(['data' => $appointmentCollection]);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user, string $appointment)
    {
        $appointmentInstance = Appointment::select([
            'appointments.*',
            'services.title',
            'services.category',
            'masters.firstname as master_firstname',
            'masters.lastname as master_lastname',
            'schedules.date',
            'schedules.time',
        ])
            ->join('schedules', 'appointments.schedule_id', '=', 'schedules.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->join('users as masters', 'schedules.master_id', '=', 'masters.id')
            ->where('appointments.client_id', '=', $user)
            ->where('appointments.id', '=', $appointment)
            ->first();
        if (!empty($appointmentInstance)) {
            $appointmentResource = new AppointmentClientResource($appointmentInstance);
            return response()->json(['data' => $appointmentResource]);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user, string $appointment)
    {
        $appointmentInstance = Appointment::where('id', $appointment)->where('client_id', $user)->first();
        if (!$appointmentInstance) {
            return response()->json(['message' => 'No data'], 404);
        }
        $appointmentInstance->schedule()->update(['status' => 'available']);
        $appointmentInstance->delete();
        return response()->json(['message' => 'Appointment successfully deleted']);
    }
}
