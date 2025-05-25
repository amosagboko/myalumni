<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsurePaymentsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->alumni) {
            $alumni = Auth::user()->alumni;
            
            // Only enforce for 2023 and earlier graduates
            if ($alumni->year_of_graduation <= 2023) {
                // Get active fees and check if any are unpaid
                $hasUnpaidFees = $alumni->getActiveFees()->contains(function($fee) {
                    return !$fee->isPaid();
                });

                if ($hasUnpaidFees) {
                    // Exclude payment-related routes and logout route from redirect
                    if (!$request->is('payments*') && !$request->is('logout')) {
                        Log::info('Redirecting alumni to payments page', [
                            'alumni_id' => $alumni->id,
                            'year_of_graduation' => $alumni->year_of_graduation,
                            'current_route' => $request->route()->getName()
                        ]);
                        
                        return redirect()->route('alumni.payments.index')
                            ->with('warning', 'Please complete your payments to access this feature.');
                    }
                }
            }
        }

        return $next($request);
    }
} 