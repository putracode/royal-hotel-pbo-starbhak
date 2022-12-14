<?php

namespace App\Http\Controllers\Backend;

use App\Models\Room;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(){
        $booking = Booking::all()->sortByDesc('created_at');
        $user = User::all();
        $room = Room::all();
        return view('admin.booking.index',[
            'booking' => $booking,
            'user' => $user,
            'room' => $room
        ]);
    }

    public function create(){
        $user = User::where('role','user')->orderBy('created_at','desc')->get();
        return view('admin.booking.create',[
            'booking' => Booking::all(),
            // 'user' => User::all()->sortBy('name'),
            'user' => $user,
            'room' => Room::all()->sortBy('room_type')
        ]);
    }

    function GenerateCode()
    {

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $codeLength = 6;

        $code = '';

        while (strlen($code) < 6) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code.$character;
        }

        if (Booking::where('booking_code', $code)->exists()) {
            $this->GenerateCode();
        }

        return $code;
    }

    public function store(Request $request){
        $validasi = $this->validate($request,[
            'user_id' => ['required'],
            'room_id' => ['required'],
            'check_in' => ['required'],
            'check_out' => ['required'],
            'qty' => ['required'],
        ]);

        $room = Room::where('id',$request->room_id)->first();
        $validasi['total_payment'] = $request->qty * $room->price;
        $validasi['booking_code'] = $this->GenerateCode();

        Booking::create($validasi);
        return redirect('/admin/booking')->with('success','Data berhasil di tambah!');
    }

    public function edit($id){
        return view('booking.edit',[
            'booking' => Booking::find($id),
            'user' => User::all()->sortBy('name'),
            'room' => Room::all()->sortBy('room_type')
        ]);
    }

    public function update(Request $request,$id){
        $validasi = $this->validate($request,[
            'user_id' => ['required'],
            'room_id' => ['required'],
            'check_in' => ['required'],
            'check_out' => ['required'],
            'total_payment' => ['required'],
            'qty' => ['required'],
        ]);

        Booking::where('id',$id)->update($validasi);
        return redirect('/admin/booking')->with('Edit','Data berhasil di ubah!');
    }

    public function destroy($id){
        $booking = Booking::find($id);
        $booking->delete();

        return redirect('/admin/booking')->with('Delete','Data berhasil di hapus!');
    }

    public function filter(Request $request){
        if (request()->dari || request()->sampai) {
            // $dari = explode('-', request('dari'));
            // $dari = $dari[0]. '-' . $dari[1] . '-' . intval($dari[2]) + 1;

            $sampai = explode('-', request('sampai'));
            $sampai = $sampai[0]. '-' . $sampai[1] . '-' . intval($sampai[2]);
            
            $booking = Booking::whereBetween('check_in',[request('dari'), $sampai])->whereBetween('check_out',[request('dari'), $sampai])->get();
            // $booking = Booking::whereBetween('check_in',request('dari'))->whereBetweer('check_out',$sampai)->get();
        } else {
            $booking = Booking::latest()->get();
        }

        return view('admin.booking.index',[
            'booking' => $booking
        ]);
    }
}
