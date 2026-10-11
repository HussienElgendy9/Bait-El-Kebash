@extends('layouts.app')

@section('content')
 <div class="w-80 py-4 object-cover rounded-xl transform transition-all hover:-translate-y-2 duration-300 shadow-lg hover:shadow-2xl gap-3 border border-brand-beige flex flex-col items-center bg-white">
    
                <h3>{{ $user->name }}</h3>
                <p>{{$user->email}}</p>
                <p>{{$user->phone_number}}</p>
                {{-- <p>{{$categories->find($user->category_id)->name}}</p> --}}
                <p>{{$user->role}}</p>
                
                <div class="flex items-center gap-2  flex-col justify-center lg:flex-row">
                    <a href="{{ route('admin.users.show', $user->id) }}"
                                   class="bg-green-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
                                    View
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
                                    Edit
                                </a>

                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        onclick="return confirm('Delete this product?')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">
                                        Delete
                                    </button>
                    
                </div>
            </div>
@endsection