<?php



namespace App\Http\Controllers\AlumniPresident;



use App\Http\Controllers\Controller;

use App\Models\Event;

use Illuminate\Support\Facades\Auth;

use Illuminate\View\View;



class DutiesController extends Controller

{

    public function index(): View

    {

        $user = Auth::user();



        $communityEvents = Event::query()

            ->where('user_id', $user->id)

            ->where('type', 'opportunity')

            ->orderByDesc('created_at')

            ->limit(5)

            ->get();



        $eventStats = [

            'total' => Event::query()

                ->where('user_id', $user->id)

                ->where('type', 'opportunity')

                ->count(),

            'published' => Event::query()

                ->where('user_id', $user->id)

                ->where('type', 'opportunity')

                ->where('is_published', true)

                ->count(),

            'pending' => Event::query()

                ->where('user_id', $user->id)

                ->where('type', 'opportunity')

                ->where('is_published', false)

                ->count(),

        ];



        return view('alumni-president.duties', compact(

            'communityEvents',

            'eventStats',

        ));

    }

}


