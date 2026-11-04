@extends('layouts.public')

@section('title', 'Contact - Test')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-4">Page de Contact - Test</h1>
            <p class="text-center">Si vous voyez ce message, la page fonctionne !</p>
            
            <div class="card">
                <div class="card-body">
                    <h3>Informations de Contact</h3>
                    <p><strong>Adresse:</strong> Rue El Hadji Amadou Assane Ndoye, 29, Dakar, Sénégal</p>
                    <p><strong>Téléphone:</strong> +221 77 645 92 42</p>
                    <p><strong>Email:</strong> contact@csar.sn</p>
                    <p><strong>Site web:</strong> www.csar.sn</p>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h3>Formulaire de Contact</h3>
                    <form action="{{ route('contact.submit', ['locale' => app()->getLocale()]) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom complet *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Sujet *</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
