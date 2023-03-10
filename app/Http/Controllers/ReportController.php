<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbonx;
use PDF;
use Excel;
use App\Exports\ReportsExport;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function exportPDF()
     {
        //ambil data yang akan ditampilkan pada pdf, bisa juga dengan where atau eloquent lainnya dan jangan gunakan pagination
        //jangan lupa konvert data jadi array dengan toArray()
        $data = Report::with('response')->get()->toArray();
        //kirim data yang diambil kepada view yang akan ditampilkan, kirim dengan inisial
        view()->share('reports', $data);
        //panggil view blade yang akan dicetak PDF serta data yang akan digunakan
        $pdf = PDF::loadView('print', $data)->setPaper('a4', 'landscape'); //$data=> data yang dipanggil dari bladenya
        // download PDF file dengan nama tertentu
        return $pdf->download('data_pengaduan_keseluruhan.pdf');
     }
     public function printPDF($id)
     {
          //ambil data yang akan ditampilkan pada pdf, bisa juga dengan where atau eloquent lainnya dan jangan gunakan pagination
        $data = Report::with('response')->where('id', $id)->get()->toArray();
          //kirim data yang diambil kepada view yang akan ditampilkan, kirim dengan inisial
        view()->share('reports', $data);
        //panggil view blade yang akan dicetak PDF serta data yang akan digunakan
        $pdf = PDF::loadView('print', $data);
        //download PDF file dengan nama tertentu
        return $pdf->download('data_perbaris.pdf');
     }
     public function exportExcel()
     {
        //nama file yang akan terdownload
        // selain xlsx juga bisa .csv
        $file_name =
        'data_keseluruhan_pengaduan.xlsx';
        //memanggil file ReportsExport dan mengdownloadnya dengan nama seperti $file_name
        return Excel::download(new ReportsExport, $file_name);
     }
    public function index()
    {
        // ASC : ascending -> terkecil 1-100 / a-z
        // DESC : descending -> terbesar terkecil 100-1/ z-a
        $reports = Report::orderBy('created_at', 'DESC')->simplePaginate(2);
        return view('index', compact ('reports'));
    }
    // Request $request ditambahkan karna pada halaman data ada fitur serach nya, dan akan mengambil teks yang diinput search
    public function data(Request $request)
    {
        //ambil data yang diinput ke input yang namanya search
        $search = $request->search; 
        // where akan mencari data berdasarkan column data
        // data yang akan diambil merupakan data yang 'LIKE' (terdapat) teks yang dimasukin ke input search
        // contoh: ngisi input search dengan 'fem'
        //bakal nyari ke db yang column namanya ada isi 'fem' nya
        $reports = Report:: with('response')->where('nama', 'LIKE', '%' . $search .'%')->orderBy('created_at', 'DESC')->get();
        return view('data', compact('reports'));
    }
    public function dataPetugas(Request $request)
    {
        $search = $request-> search;
        //with : ambil relasi (nama fungsi hasOne/hasMany/belongsTo di modelnya), ambil data dari relasi itu
        $reports = Report::with('response')->where('nama', 'LIKE', '%' . $search . '%')->orderBy('created_at', 'DESC')->get();
        return view('data_petugas', compact('reports'));
    }
    public function auth(Request $request)
    {
        $request->validate([
            'email' => 'required|email:dns',
            'password' => 'required',
        ]);
        //ambil data dan simpan di variable
        $user = $request->only('email', 'password');
        //simpen data ke auth dengan Auth:attempt
        //cek proses penyimpanan ke auth berhasil atau tidak lewat if else
        if (Auth::attempt($user)) {
            // nesting if, if bersarang, if didalam if
            // kalau data Auth tersebut role nya admin maka masuk ke route data
            // kalau data Auth role nya petugas maka masuk ke  route data.petugas
            if (Auth::user()->role == 'admin'){
                return redirect()->route ('data');
        }elseif(Auth::user()->role == 'petugas'){ 
            return redirect()->route('data.petugas');
        }
        }else {
            return redirect()->back()->with('gagal', 'Gagal login, coba lagi');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'nama' => 'required',
            'no_telp' => 'required',
            'pengaduan' => 'required',
            'foto' => 'required|image|mimes:jpg,jpeg,png,svg'
        ]);
        //pindah foto ke folder public
        $path = public_path('assets/image');
        $image = $request->file('foto');
        $imgName = rand() . "." . $image->extension();
        $image->move($path, $imgName) ;

        //tambah data ke db
        Report::create([
            'nik' => $request->nik,
            'nama' => $request->nama,
            'no_telp' => $request->no_telp,
            'pengaduan' => $request->pengaduan,
            'foto' => $imgName,
        ]);
        return redirect()->back()->with('success', 'Berhasil menambahkan pengaduan!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //cari data yang dimaksud, where buat nyari data nya firstorfail= ngambil data satu, kalo ada where ada berarti dia ngambil 1 baris aja
        $data = Report::where('id', $id)->firstOrfail();
        // $data isinya -> nik sampe foto dari pengaduan
        //hapus data foto dari folder public : path, nama fotonya
        //nama foto nya diambil dari $data yang di atas terus ngambil dari column foto
        //public path nyari file di folder public yang namanya sama kaya $data bagian foto
        $image = public_path('assets/image/' .$data['foto']);
        //uda nemu posisi fotonya, tinggal dihapus fotonya dari public nya pake unlink
        unlink($image);
        //hapus data dari database
        $data->delete();
        // setelahnya balikin lagi ke halaman awal
        Response::where('report_id', $id)->delete();
        return redirect()->back();
    }
}
?>