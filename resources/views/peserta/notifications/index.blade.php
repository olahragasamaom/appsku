@extends('peserta.layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Notifikasi</h1>
                <p class="text-slate-600 mt-1">Semua update terkait aktivitas akun Anda</p>
            </div>
            @if($notifications->whereNull('read_at')->isNotEmpty())
                <form method="POST" action="{{ route('peserta.notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="card">
            <div class="divide-y divide-slate-200">
                @forelse($notifications as $notif)
                    <a href="{{ route('peserta.notifications.show', $notif) }}" 
                       class="block px-6 py-4 hover:bg-slate-50 transition-colors {{ $notif->isUnread() ? 'bg-primary-50/30' : '' }}">
                        <div class="flex items-start gap-4">
                            {{-- Icon --}}
                            @if($notif->type === 'verified')
                                <div class="w-12 h-12 rounded-full bg-success-100 text-success-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                </div>
                            @elseif($notif->type === 'rejected')
                                <div class="w-12 h-12 rounded-full bg-danger-100 text-danger-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-full bg-warning-100 text-warning-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                </div>
                            @endif

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-slate-900">{{ $notif->title }}</h3>
                                        <p class="text-sm text-slate-600 mt-1">{{ $notif->message }}</p>
                                        @if($notif->type === 'rejected' && isset($notif->data['alasan']))
                                            <div class="mt-2 p-3 bg-danger-50 border border-danger-200 rounded-lg">
                                                <p class="text-xs text-danger-700 font-medium">Alasan:</p>
                                                <p class="text-sm text-danger-800 mt-1">{{ $notif->data['alasan'] }}</p>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-3 mt-2 text-xs text-slate-500">
                                            <span>{{ $notif->created_at->translatedFormat('d F Y H:i') }}</span>
                                            <span>•</span>
                                            <span>{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    @if($notif->isUnread())
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-primary-700 bg-primary-100 px-2 py-1 rounded-full flex-shrink-0">
                                            <div class="w-2 h-2 rounded-full bg-primary-600"></div>
                                            Baru
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <h3 class="font-semibold text-slate-700 mb-2">Belum Ada Notifikasi</h3>
                        <p class="text-sm text-slate-500">Notifikasi tentang pembayaran dan aktivitas Anda akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
