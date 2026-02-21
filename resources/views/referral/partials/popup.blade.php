@if(!empty($referralPopup))
    <div id="referral-popup-modal" class="fixed bottom-4 right-4 z-[95] w-[calc(100%-2rem)] max-w-sm">
        <div class="rounded-xl bg-white/95 border border-gray-200 shadow-2xl backdrop-blur-sm">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $referralPopup['title'] }}</h3>
            </div>
            <div class="px-4 py-3 text-xs text-gray-700 whitespace-pre-line max-h-40 overflow-y-auto">{{ $referralPopup['body'] }}</div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end">
                <form method="POST" action="{{ $dismissRoute }}">
                    @csrf
                    <input type="hidden" name="popup_hash" value="{{ $referralPopup['hash'] }}">
                    <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md hover:bg-blue-700">
                        {{ __('Dismiss') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
