@php($p = $product ?? null)
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informations du produit</h6>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $p->name ?? '') }}" placeholder="Ex : Riz brisé" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="category" class="form-label">Catégorie</label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category"
                               value="{{ old('category', $p->category ?? '') }}" placeholder="Ex : Céréales" list="categories">
                        <datalist id="categories">
                            <option value="Céréales"><option value="Légumineuses"><option value="Huiles"><option value="Divers">
                        </datalist>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('type') is-invalid @enderror" id="type" name="type"
                               value="{{ old('type', $p->type ?? '') }}" placeholder="Ex : Céréales" required>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="unit" class="form-label">Unité <span class="text-danger">*</span></label>
                        <select class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                            @foreach(['kg' => 'Kilogramme (kg)', 'L' => 'Litre (L)', 'sac' => 'Sac', 'unité' => 'Unité'] as $val => $label)
                                <option value="{{ $val }}" {{ old('unit', $p->unit ?? 'kg') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="unit_price" class="form-label">Prix unitaire (FCFA)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('unit_price') is-invalid @enderror" id="unit_price" name="unit_price"
                               value="{{ old('unit_price', $p->unit_price ?? '') }}">
                        @error('unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="image" class="form-label">Photo du produit</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Formats : JPEG, PNG, WebP. Taille maximale : 5MB. Cette photo s'affiche dans l'application magasinier.</div>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if($p && $p->image_url)
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                        <label class="form-check-label" for="remove_image">Supprimer la photo actuelle</label>
                    </div>
                @endif

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $p->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Produit actif</label>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary me-2">Annuler</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Aperçu</h6>
            </div>
            <div class="card-body text-center" id="image-preview">
                @if($p && $p->image_url)
                    <img src="{{ $p->image_url }}" class="img-fluid rounded" alt="{{ $p->name }}" style="max-height: 220px; object-fit: cover;">
                @else
                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Aucune photo</p>
                @endif
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('image').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (ev) {
        document.getElementById('image-preview').innerHTML =
            '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height: 220px; object-fit: cover;">';
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
