<div class="modal fade" id="editFolderModal{{ $folder->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('folders.update', $folder) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Renommer le dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du dossier <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $folder->name }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dossier parent</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- Aucun (racine) --</option>
                            @foreach($folders->where('id', '!=', $folder->id)->where('parent_id', '!=', $folder->id) as $f)
                                @if($f->id !== $folder->id)
                                    <option value="{{ $f->id }}" {{ $folder->parent_id == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
