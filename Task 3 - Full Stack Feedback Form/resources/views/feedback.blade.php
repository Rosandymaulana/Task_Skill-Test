<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    @vite('resources/css/app.css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-lg shadow-xl p-8 mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">Formulir Feedback!</h2>

            <form id="feedbackForm" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="name" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" id="email" placeholder="your@email.com" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Komentar</label>
                    <textarea name="comment" id="comment" rows="4" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm resize-none"></textarea>
                </div>
                <button type="submit" id="submitBtn" disabled
                    class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white transition duration-150 ease-in-out">
                    Kirim Feedback
                </button>
            </form>
        </div>

        <div class="w-full max-w-md bg-white rounded-lg shadow-xl p-8">
            <h3 class="text-2xl font-extrabold text-gray-900 mb-6 text-center">Feedback dari Pengguna Lain</h3>
            <ul id="feedbackList" class="space-y-4">
                <li class="p-4 bg-gray-50 rounded-md shadow-sm text-gray-700 text-center">Belum ada feedback. Jadilah yang pertama!</li>
            </ul>
        </div>
    </div>

    <script>
        const form = document.getElementById('feedbackForm');
        const feedbackList = document.getElementById('feedbackList');
        const inputs = form.querySelectorAll('input, textarea');
        const submitBtn = document.getElementById('submitBtn');

        async function loadFeedback() {
            try {
                const res = await fetch('/api/feedback');
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                const data = await res.json();

                if (data.length === 0) {
                    feedbackList.innerHTML = '<li class="p-4 bg-gray-50 rounded-md shadow-sm text-gray-700 text-center">Belum ada feedback. Jadilah yang pertama!</li>';
                    return;
                }

                feedbackList.innerHTML = data.map(f => `
                    <li class="bg-blue-50 border border-blue-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <strong class="text-blue-800 text-lg">${f.name}</strong>
                            <span class="text-gray-500 text-sm">${f.email}</span>
                        </div>
                        <p class="text-gray-800">${f.comment}</p>
                        <small class="text-gray-500 text-xs block mt-2 text-right">Dikirim pada: ${new Date(f.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}</small>
                    </li>
                `).join('');
            } catch (error) {
                console.error("Error loading feedback:", error);
                feedbackList.innerHTML = '<li class="p-4 bg-red-50 border border-red-200 rounded-md text-red-700 text-center">Gagal memuat feedback.</li>';
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            if (!payload.name || !payload.email || !payload.comment) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Semua kolom wajib diisi!'
                });
                return;
            }

            try {
                const res = await fetch('/api/feedback', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    form.reset();
                    loadFeedback();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Feedback berhasil dikirim!'
                    });
                } else {
                    const err = await res.json();
                    let errorMessages = Object.values(err.errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessages
                    });
                }
            } catch (error) {
                console.error("Error submitting feedback:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan saat mengirim Feedback. Silakan coba lagi.'
                });
            }
        });

        function checkFormValidity() {
            const isFormValid = Array.from(inputs).every(input => input.checkValidity());

            if (isFormValid) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'opacity-50');
                submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-50');
                submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
            }
        }

        form.addEventListener('input', checkFormValidity);
        checkFormValidity();
        loadFeedback();
    </script>
</body>

</html>