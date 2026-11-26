<?php

namespace App\Http\Controllers\StockOperations;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Gestion des inspecteurs et magasiniers (création, modification, désactivation,
 * rattachement au magasin) pour DG / DTL / Admin. Chaque action est historisée
 * dans audit_logs (action « staff.* »). Aucune suppression de compte.
 */
class StockStaffController extends Controller
{
    private const MANAGE_ROLES = ['dtl', 'dg', 'admin', 'super_admin'];
    private const STAFF_ROLES = ['inspecteur', 'magasinier'];

    private function actor(): User
    {
        $u = Auth::user();
        abort_unless($u && in_array($u->role, self::MANAGE_ROLES, true), 403);

        return $u;
    }

    private function portal(Request $request): string
    {
        $path = $request->path();

        return str_starts_with($path, 'dtl/') ? 'dtl' : (str_starts_with($path, 'admin') ? 'admin' : 'dg');
    }

    private function back(string $portal)
    {
        return redirect()->route($portal . '.stock-staff.index');
    }

    private function staff(int $id): User
    {
        $u = User::findOrFail($id);
        abort_unless(in_array($u->role, self::STAFF_ROLES, true), 403, 'Compte hors périmètre (inspecteur/magasinier).');

        return $u;
    }

    private function log(User $actor, string $action, User $target, string $desc): void
    {
        AuditLog::create([
            'user_id' => $actor->id, 'action' => $action, 'level' => 'info', 'status' => 'success',
            'model_type' => User::class, 'model_id' => $target->id, 'description' => $desc . " (par {$actor->name})",
        ]);
    }

    public function index(Request $request)
    {
        $this->actor();
        $portal = $this->portal($request);

        return view('stock-operations.staff', [
            'direction' => $portal,
            'directionLabel' => 'Personnel stock',
            'portal' => $portal,
            'inspectors' => User::where('role', 'inspecteur')->orderByDesc('is_active')->orderBy('name')->get(),
            'keepers' => User::where('role', 'magasinier')->with('warehouse:id,name,region')->orderByDesc('is_active')->orderBy('name')->get(),
            'warehouses' => Warehouse::where('status', 'active')->orderBy('region')->orderBy('name')->get(['id', 'name', 'region']),
            'history' => AuditLog::where('action', 'like', 'staff.%')->orderByDesc('id')->limit(50)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor();
        $data = $request->validate([
            'role' => 'required|in:inspecteur,magasinier',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'warehouse_id' => 'required_if:role,magasinier|nullable|exists:warehouses,id',
        ]);

        $password = Str::password(12);
        $user = new User([
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'],
            'is_active' => true, 'password' => Hash::make($password),
            'warehouse_id' => $data['role'] === 'magasinier' ? $data['warehouse_id'] : null,
        ]);
        $user->role = $data['role'];
        $user->save();
        $this->log($actor, 'staff.create', $user, "Création {$data['role']} {$user->email}" . ($user->warehouse_id ? " (magasin #{$user->warehouse_id})" : ''));

        return $this->back($this->portal($request))
            ->with('success', "Compte {$data['role']} créé : {$user->email}")
            ->with('generated_password', $password);
    }

    public function update(Request $request, int $id)
    {
        $actor = $this->actor();
        $user = $this->staff($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        $old = $user->warehouse_id;
        $user->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']]);
        if ($user->role === 'magasinier') {
            abort_unless(!empty($data['warehouse_id']), 422, 'Un magasinier doit être rattaché à un magasin.');
            $user->warehouse_id = $data['warehouse_id'];
        }
        $user->save();

        $desc = "Modification {$user->role} {$user->email}";
        if ($user->role === 'magasinier' && (int) $old !== (int) $user->warehouse_id) {
            $desc .= " — magasin #{$old} → #{$user->warehouse_id}";
        }
        $this->log($actor, 'staff.update', $user, $desc);

        return $this->back($this->portal($request))->with('success', 'Compte mis à jour.');
    }

    public function toggle(Request $request, int $id)
    {
        $actor = $this->actor();
        $user = $this->staff($id);
        abort_if($user->id === $actor->id, 422);

        $user->is_active = !$user->is_active;
        $user->save();
        if (!$user->is_active) {
            $user->tokens()->delete();
        }
        $this->log($actor, $user->is_active ? 'staff.activate' : 'staff.deactivate', $user,
            ($user->is_active ? 'Réactivation ' : 'Désactivation ') . "{$user->role} {$user->email}");

        return $this->back($this->portal($request))->with('success', $user->is_active ? 'Compte réactivé.' : 'Compte désactivé (sessions mobiles révoquées).');
    }
}
