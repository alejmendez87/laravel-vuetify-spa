<?php

namespace App\Http\Controllers\Admin;

// Dependencies
use Illuminate\Http\Request;

// Controller parent
use App\Http\Controllers\ApiController;

// Third party package
use Idsign\Vuetify\Facades\Datatable;

// Models
use App\User;

class UsersController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = User::select('id', 'name', 'email');
        return Datatable::eloquent($query)->make(true);
        return $this->datatable($request, $query);
    }

    protected function datatable(Request $request, $query) {
        $sortBy = $request->sortBy;
        $descending = $request->descending;
        $page = $request->page;
        $rowsPerPage = $request->rowsPerPage;
        $search = $request->search;
        $orderedBy = $descending ? 'desc' : 'asc';

        $start = ($page - 1) * $rowsPerPage;

        $total = $query->count();

        $query
            ->orderBy($sortBy, $orderedBy)
            ->skip($start)
            ->take($rowsPerPage);

        $column = ['name', 'email'];
        if ($search) {
            foreach ($column as $col) {
                $query->orWhere($col, 'LIKE','%'.$search.'%');
            }
        }

        $data = [];
        foreach ($query->get() as $fila) {
            $data[] = $fila->toArray();
        }

        return $this->successResponse([
            'data'  => $data,
            'total' => $total,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = User::create($request);
        return $this->showOne($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'min:4',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6',
            'super' => 'in:0,1',
        ]);

        $user->fill($request->all());

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = false;
            $user->verification_token = User::generateVerificationToken();
        }

        $user->save();
        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->showOne($user);
    }
}
