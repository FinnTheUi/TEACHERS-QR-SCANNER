@extends('layouts.app')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Teacher QR Keys</h2>
            <button type="button" onclick="openCreateModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Create New Key
            </button>
        </div>

        <!-- Keys List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Used</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($keys as $key)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="qr-code">
                                    {!! QrCode::size(100)->generate($key->key_code) !!}
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $key->description }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $key->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $key->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-6 py-4">{{ $key->expires_at ? $key->expires_at->format('Y-m-d') : 'Never' }}</td>
                            <td class="px-6 py-4">
                                <button onclick="openEditModal({{ $key->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                <button onclick="deleteKey({{ $key->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No QR keys found. Create your first key to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $keys->links() }}
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="keyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Create New Key</h3>
            <form id="keyForm" class="mt-4">
                @csrf
                <input type="hidden" id="keyId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Description
                    </label>
                    <input type="text" id="description" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="expires_at">
                        Expiration Date
                    </label>
                    <input type="date" id="expires_at" name="expires_at" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" class="form-checkbox" checked>
                        <span class="ml-2 text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR codes for existing keys
    document.querySelectorAll('.qr-code').forEach(function(element) {
        new QRious({
            element: element,
            value: element.dataset.code,
            size: 100
        });
    });

    // Setup form submission
    document.getElementById('keyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const keyId = document.getElementById('keyId').value;
        const isEdit = !!keyId;
        
        const formData = {
            description: document.getElementById('description').value,
            expires_at: document.getElementById('expires_at').value || null,
            is_active: document.getElementById('is_active').checked,
        };

        if (!isEdit) {
            formData.key_code = generateUniqueCode();
        }

        const url = isEdit 
            ? `/teacher-keys/${keyId}`
            : '/teacher-keys';

        fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});

function generateUniqueCode() {
    return 'QR-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create New Key';
    document.getElementById('keyId').value = '';
    document.getElementById('keyForm').reset();
    document.getElementById('keyModal').classList.remove('hidden');
}

function openEditModal(keyId) {
    document.getElementById('modalTitle').textContent = 'Edit Key';
    document.getElementById('keyId').value = keyId;
    
    fetch(`/teacher-keys/${keyId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('description').value = data.description || '';
            document.getElementById('expires_at').value = data.expires_at ? data.expires_at.split('T')[0] : '';
            document.getElementById('is_active').checked = data.is_active;
            document.getElementById('keyModal').classList.remove('hidden');
        });
}

function closeModal() {
    document.getElementById('keyModal').classList.add('hidden');
    document.getElementById('keyForm').reset();
}

function deleteKey(keyId) {
    if (!confirm('Are you sure you want to delete this key?')) {
        return;
    }

    fetch(`/teacher-keys/${keyId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
@endpush
