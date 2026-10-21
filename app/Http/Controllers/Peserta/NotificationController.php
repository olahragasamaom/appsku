<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\PaymentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = PaymentNotification::with('pembayaran.paket')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('peserta.notifications.index', compact('notifications'));
    }

    public function show(Request $request, PaymentNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        $notification->markAsRead();

        if ($notification->pembayaran_id) {
            return redirect()->route('peserta.langganan.status', $notification->pembayaran_id);
        }

        return redirect()->route('peserta.notifications.index');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        PaymentNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
