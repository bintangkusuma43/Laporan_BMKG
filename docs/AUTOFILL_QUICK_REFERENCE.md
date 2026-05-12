# AUTOFILL PETUGAS - QUICK REFERENCE

## 🚀 Quick Start (Copy-Paste Ready)

### 1. PHP Endpoint (Production: Sudah ada di api/reference/master_petugas_json.php)
```php
<?php
// GET /api/reference/master_petugas_json.php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/_master_petugas_data.php';

$data = array_map(function($item) {
    return [
        'nama' => $item['nama'] ?? '',
        'nip' => $item['nip'] ?? '',
    ];
}, $MASTER_PETUGAS);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
```

### 2. HTML Form (Minimal)
```html
<input type="text" class="nama" id="nama_petugas" list="list_nama" autocomplete="off">
<input type="text" class="nip" id="nip_petugas" list="list_nip" autocomplete="off">
```

### 3. JavaScript Binding
```javascript
// Import script di HTML
<script src="/laporan_bmkg/assets/js/petugasAutofill.js"></script>

// Kemudian dalam script Anda:
document.addEventListener('DOMContentLoaded', () => {
    const namaInput = document.querySelector('.nama');
    const nipInput = document.querySelector('.nip');
    
    PetugasAutofill.bindRow(namaInput, nipInput, {
        onSelect: ({nama, nip}) => {
            console.log('User selected:', nama, nip);
        }
    });
});
```

## 📚 API Functions

### loadMasterPegawai()
Load master data dari server
```javascript
PetugasAutofill.loadMasterPegawai()
    .then(data => console.log('Loaded:', data.length, 'entries'))
    .catch(err => console.error('Failed:', err));
```

### bindRow(namaInput, nipInput, options)
Bind autofill ke pair input **[MAIN FUNCTION]**
```javascript
// Basic
PetugasAutofill.bindRow(namaEl, nipEl);

// Dengan callback
PetugasAutofill.bindRow(namaEl, nipEl, {
    onSelect: ({nama, nip}) => {
        alert(`Selected: ${nama} (${nip})`);
    }
});
```

### filterPegawai(list, query)
Filter daftar petugas
```javascript
const results = PetugasAutofill.filterPegawai(masterData, 'ARDHI');
console.log(results); // [{nama: '...', nip: '...'}]
```

### findByNama(list, nama)
Cari petugas berdasarkan nama (exact match)
```javascript
const petugas = PetugasAutofill.findByNama(masterData, 'ARDHIANTO SEPTIADHI, S.Si., M.T.');
console.log(petugas.nip); // '198109292006041007'
```

### findByNip(list, nip)
Cari petugas berdasarkan NIP (exact match)
```javascript
const petugas = PetugasAutofill.findByNip(masterData, '198109292006041007');
console.log(petugas.nama); // 'ARDHIANTO SEPTIADHI, S.Si., M.T.'
```

### normalizeText(text)
Normalize text (lowercase, trim)
```javascript
PetugasAutofill.normalizeText('  ARDHIANTO  '); // 'ardhianto'
```

### getState()
Debug: get current state
```javascript
const state = PetugasAutofill.getState();
console.log(state);
// {
//   loaded: true,
//   dataCount: 43,
//   data: [{...}, {...}, ...]
// }
```

## 🔧 Implementation Patterns

### Pattern 1: Single Row (Dialog/Modal)
```javascript
const namaInput = document.querySelector('.nama');
const nipInput = document.querySelector('.nip');
PetugasAutofill.bindRow(namaInput, nipInput);
```

### Pattern 2: Multiple Rows (Dynamic)
```javascript
function createPetugasRow() {
    const rowId = Math.random().toString(36).substr(2, 9);
    const html = `
        <div class="row" id="row_${rowId}">
            <input type="text" class="nama" list="list_nama_${rowId}" autocomplete="off">
            <input type="text" class="nip" list="list_nip_${rowId}" autocomplete="off">
        </div>
    `;
    document.querySelector('#petugas_container').insertAdjacentHTML('beforeend', html);
    
    const row = document.querySelector(`#row_${rowId}`);
    PetugasAutofill.bindRow(
        row.querySelector('.nama'),
        row.querySelector('.nip')
    );
}
```

### Pattern 3: Form Submit Validation
```javascript
document.querySelector('form').addEventListener('submit', (e) => {
    e.preventDefault();
    
    const rows = document.querySelectorAll('.petugas-row');
    const valid = Array.from(rows).every(row => {
        const nama = row.querySelector('.nama').value.trim();
        const nip = row.querySelector('.nip').value.trim();
        
        // Validate: both harus ada dan sesuai master
        if (!nama || !nip) {
            alert('Nama dan NIP harus lengkap');
            return false;
        }
        
        return true;
    });
    
    if (valid) {
        // Submit form
        e.target.submit();
    }
});
```

### Pattern 4: Pre-fill Existing Data
```javascript
async function prefillPetugasData(nama, nip) {
    const namaInput = document.querySelector('.nama');
    const nipInput = document.querySelector('.nip');
    
    // Set values
    namaInput.value = nama;
    nipInput.value = nip;
    
    // Trigger change untuk validate
    namaInput.dispatchEvent(new Event('change'));
    nipInput.dispatchEvent(new Event('change'));
}

// Usage
prefillPetugasData('ARDHIANTO SEPTIADHI, S.Si., M.T.', '198109292006041007');
```

## ⚠️ Common Issues & Solutions

### Issue: Datalist tidak muncul
**Solution:**
```javascript
// Debug 1: Check data loaded
console.log(PetugasAutofill.getState());

// Debug 2: Check input attributes
const input = document.querySelector('.nama');
console.log('list:', input.getAttribute('list'));
console.log('id:', input.id);

// Debug 3: Check datalist exists
const list = document.getElementById('list_nama_...');
console.log('Datalist:', list);
console.log('Options:', list?.querySelectorAll('option').length);
```

### Issue: Autofill tidak autofill
**Solution:**
```javascript
// Check if master data contains the value
const masterData = await PetugasAutofill.loadMasterPegawai();
const found = PetugasAutofill.findByNama(masterData, 'YOUR_SEARCH');
console.log('Found:', found); // null = tidak ada di master

// Check normalization
const normalized1 = PetugasAutofill.normalizeText('  ARDHIANTO  ');
const normalized2 = PetugasAutofill.normalizeText(input.value);
console.log('Match:', normalized1 === normalized2);
```

### Issue: Infinite loop autofill
**Solution:**
Sudah handled oleh sync state tracking di petugasAutofill.js. Jika masih terjadi:
```javascript
// Check sync state
const input = document.querySelector('.nama');
console.log('Sync state:', syncState.get(input));

// Force clear
input.dispatchEvent(new Event('blur'));
```

### Issue: Performance slow
**Solution:**
```javascript
// Monitor datalist items
document.addEventListener('input', (e) => {
    if (e.target.classList.contains('nama')) {
        const datalist = document.getElementById(e.target.getAttribute('list'));
        const count = datalist?.querySelectorAll('option').length;
        console.log('Datalist items:', count); // Should be <= 50
    }
});

// Check network
// Network tab -> /api/reference/master_petugas_json.php
// Response time harus < 200ms
```

## 📋 Checklist Implementasi

- [ ] `master_petugas_json.php` endpoint accessible
- [ ] `petugasAutofill.js` included di HTML
- [ ] Input punya class `nama` dan `nip`
- [ ] Input punya `list` attribute
- [ ] Input punya `autocomplete="off"`
- [ ] `PetugasAutofill.bindRow()` called pada DOMContentLoaded
- [ ] Tested: manual type di field
- [ ] Tested: select dari datalist dropdown
- [ ] Tested: autofill kedua arah bekerja
- [ ] Tested: no infinite loop
- [ ] Tested: multiple rows independent
- [ ] Tested: invalid input handling
- [ ] Tested: form submission with validation

## 🧪 Test Scenarios

### Test 1: Basic Input → Autofill
```
1. Clear both fields
2. Type "ARDH" di nama field
3. Datalist should show suggestions
4. Select "ARDHIANTO SEPTIADHI, S.Si., M.T."
5. NIP field should auto-fill with "198109292006041007"
✓ PASS
```

### Test 2: NIP → Autofill Nama
```
1. Clear both fields
2. Type "1981" di NIP field
3. Datalist should show suggestions starting with 1981
4. Select one (e.g., "198109292006041007 - ARDHIANTO...")
5. Nama field should auto-fill
✓ PASS
```

### Test 3: Invalid Input
```
1. Type "INVALID_NAME_XYZ" di nama field
2. Trigger change event (blur)
3. NIP field should be cleared
✓ PASS
```

### Test 4: Edit After Select
```
1. Select petugas dari datalist
2. Both fields filled
3. Try edit nama field
4. Should NOT autofill NIP (berbeda dari step 1)
5. On blur, new autofill cycle starts
✓ PASS
```

### Test 5: Rapid Input
```
1. Type "A-R-D-H-I" quickly
2. No infinite loop atau crash
3. Datalist updates dengan each keystroke
✓ PASS
```

## 🚀 Performance Tips

1. **Caching:** Master data di-cache, subsequent loads instant
2. **Debounce:** Use native HTML5 datalist (no JS debounce needed)
3. **Limit:** Max 50 items per datalist
4. **WeakMap:** Sync state auto-cleanup untuk memory efficiency
5. **CDN:** Host petugasAutofill.js di CDN untuk faster load

## 📞 Support

- **Documentation:** `docs/AUTOFILL_PETUGAS_DOCUMENTATION.md`
- **Demo:** Open `docs/AUTOFILL_DEMO.html` in browser
- **Debug:** `PetugasAutofill.getState()` di console
- **API Response:** Check `/api/reference/master_petugas_json.php` in Network tab
