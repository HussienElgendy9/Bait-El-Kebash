@extends('layouts.app')

@section('content')
<div id="hero" class="font-arabic bg-gradient-to-b from-brand-beige to-brand-green text-brand-light flex flex-col lg:flex-row justify-evenly items-center h-screen w-full  px-4">
  
  <!-- Text side -->
  <div class="flex flex-col items-center w-full lg:w-1/2 gap-4">
    <h1 class="text-4xl sm:text-6xl lg:text-7xl">بيت الكباش</h1>
    <p class="text-xl sm:text-3xl lg:text-5xl">احلي لحمه مع بيت الكباش</p>
    <a href="{{ route('store.index') }}" class="border border-brand-beige bg-brand-green text-brand-light px-5 py-3 rounded-lg 
       hover:opacity-90 transition" >أحجز دلوقتي</a>
    
  </div>

  <!-- Image side -->
  <div class="flex justify-center items-center w-full lg:w-1/2">
    <img class="animate-softbounce max-w-[500px] w-full h-auto" src="{{ asset('images/la7ma.png') }}" alt="">
  </div>

</div>

    {{-- <div class="bg-white">1</div>
    <div class="bg-yellow-500">2</div>
    <div class="bg-cyan-500">3</div>
    <div class="bg-blue-500">4</div> --}}
</div>
<div id="cards" class="bg-gradient-to-b from-brand-green via-brand-offwhite to-brand-offwhite py-10 px-20 gap-3 bg-yellow-500 flex flex-wrap justify-evenly items-center  w-full">
    @foreach ($products as $product)
    <div class="w-80 py-4 object-cover rounded-xl transform transition-all hover:-translate-y-2 duration-300 shadow-lg hover:shadow-2xl gap-3 border border-brand-beige flex flex-col items-center bg-white">
        <img
    class="w-32 object-cover rounded-xl sm:w-40 md:w-52 h-auto"
    src="{{ $product->image ? Storage::url($product->image) : asset('images/meat.png') }}"
    alt="{{ $product->name }}">

        <h3>{{ $product->name }}</h3>
        <p>{{$product->description}}</p>
        <p>{{$categories->find($product->category_id)->name}}</p>
        <p>{{$product->unit_price}} جنيه/نص كيلو</p>
        
        <form action="{{ route('cart.add') }}" method="post">
            @csrf
            <div class="flex items-center gap-2  flex-col justify-center lg:flex-row">
                <div class="flex items items-center">
    
                
                    <button type="button" class="w-10  flex items-center justify-center border border-brand-beige rounded-lg bg-gray-100 hover:bg-gray-200 transition">-</button>
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="text" readonly name="qty" id="qtyInput" 
                    class="w-12  text-center border border-brand-beige rounded-lg" value="1">
    
                    <button type="button" class="w-10  flex items-center justify-center border border-brand-beige rounded-lg bg-gray-100 hover:bg-gray-200 transition">+</button>
                 </div>
    
                <button type="submit" class="border border-brand-beige bg-brand-green text-brand-light px-5 py-3 rounded-3xl 
                hover:opacity-90 transition">أضف إلي السلة</button>
                
            </div>
        </form>
    </div>
    @endforeach
    
    
</div>
@endsection