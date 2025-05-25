<div>
    {{-- Because she competes with no one, no one can compete with her. --}}

    <div>
                
                    
                
        <div class="mt-4">
            <!-- Comment Form -->
            <div class="mb-4">
                <form wire:submit.prevent="addComment">
                    <div class="flex items-start space-x-4">
                        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('images/default-avatar.png') }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1">
                            <textarea wire:model="comment" 
                                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                      placeholder="Write a comment..." 
                                      rows="2"></textarea>
                            @error('comment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Comment
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Comments List -->
            <div class="space-y-4">
                @foreach($comments as $comment)
                    <div class="flex items-start space-x-4">
                        <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : asset('images/default-avatar.png') }}" 
                             alt="{{ $comment->user->name }}" 
                             class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1 bg-gray-100 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold">{{ $comment->user->name }}</h4>
                                    <p class="text-gray-600">{{ $comment->comment }}</p>
                                </div>
                                @if($comment->user_id === Auth::id() || $comment->post->user_id === Auth::id())
                                    <button wire:click="deleteComment({{ $comment->id }})" 
                                            class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                {{ $comment->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
        </div>
        



        {{-- <div>

                    <div class="card-body p-0 d-flex text-grey-900">
                        <figure class="avatar me-3"><img src="{{ auth()->user()->avatar ? auth()->user()->avatar: '/images/user-8.png' }}" alt="image" class="shadow-sm rounded-circle w45"></figure>
                        <h4 class="fw-700 text-grey-900 font-xssss mt-1"> Amos <span class="d-block font-xssss fw-500 mt-1 lh-3 text-grey-500"> Date </span></h4>
                        
                    </div>
                    <div>
                        <div class="card-body p-10 me-lg-5 text-grey-600 bg-greylight font-xss">
                            <p>&nbsp;&nbsp; By his grace all shall be well!!! </a></p>
                        </div>
                    </div>

                    @endforeach  

                    
                    
                 
            
        </div> --}}
    </div>

</div>
