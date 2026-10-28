<h1>Zapytanie ze strony Home Kreator</h1>
<p><strong>Imię i nazwisko:</strong> {{ $inquiry['name'] }}</p>
<p><strong>E-mail:</strong> {{ $inquiry['email'] }}</p>
<p><strong>Telefon:</strong> {{ $inquiry['phone'] ?? 'Nie podano' }}</p>
<p><strong>Miejscowość:</strong> {{ $inquiry['city'] }}</p>
<p><strong>Model:</strong> {{ $inquiry['model'] ?? 'Projekt indywidualny' }}</p>
<p style="white-space: pre-wrap">{{ $inquiry['message'] }}</p>
