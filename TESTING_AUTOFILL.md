# Testing Petugas Autofill System

**Status:** Sistem autofill sudah selesai dan ready for testing

## Perubahan Terbaru (Latest Changes)

### ✅ Completed
1. **petugasAutofill.js** - Centralized autofill module with position: fixed solution
2. **Master Data** - 45 petugas entries in `/api/reference/_master_petugas_data.php`
3. **API Endpoint** - `/api/reference/petugas.php` returns JSON data
4. **All 7 Pages Updated**:
   - laporan_form.php (combined form for all 3 types)
   - laporan_form_accelerograph.php
   - laporan_form_seismograph.php  
   - laporan_form_wrs.php
   - laporan_edit_accelerograph.php
   - laporan_edit_seismograph.php
   - laporan_edit_wrs.php

5. **Code Cleanup** - Removed all redundant autofill functions from 6 pages, keeping only minimal wrapper code

## What to Test

### Test 1: WRS NG Form
**URL:** `http://localhost/laporan_bmkg/laporan_form_wrs.php`

1. Click "Tambah Petugas Pelaksana" button
2. In "Nama" field, type any name (e.g., "ARDHIANTO")
   - ✅ Should see dropdown suggestions appear
   - ✅ Should show format: "ARDHIANTO SEPTIADHI (NIP: 123456)"
3. Click on suggestion
   - ✅ Nama field should populate: "ARDHIANTO SEPTIADHI"
   - ✅ NIP field should auto-populate: "123456"
4. Try NIP field instead:
   - Clear or create new row
   - Type NIP (e.g., "123456")
   - ✅ Should see dropdown with matching records
   - ✅ Click suggestion → both NIP and Nama auto-fill

### Test 2: Seismograph Form
**URL:** `http://localhost/laporan_bmkg/laporan_form_seismograph.php`

Repeat same tests as WRS NG (should work identically)

### Test 3: Combined Form (All 3 Types)
**URL:** `http://localhost/laporan_bmkg/laporan_form.php`

1. Select "WRS NG" from dropdown
2. Add petugas → test autofill works
3. Switch to "Accelerograph"
4. Add petugas → test autofill works
5. Switch to "Seismograph"
6. Add petugas → test autofill works

### Test 4: Edit/Draft Pages
**URL:** `http://localhost/laporan_bmkg/laporan_edit_accelerograph.php?id=<report_id>`

1. Open existing report (use any existing ID)
2. ✅ Existing petugas rows should show nama & NIP pre-filled
3. Try adding new petugas row
   - ✅ Autofill should work for new rows
4. Try editing petugas field values
   - ✅ Suggestions should appear when typing

## Troubleshooting

### Issue: No suggestions appear when typing
**Solution:**
1. Open browser DevTools (F12)
2. Go to Console tab
3. Type: `console.log(BASE_URL)` → Should print full URL
4. Type: `console.log(typeof PetugasAutofill)` → Should print "object"
5. Check Network tab:
   - Filter: XHR
   - Type in any petugas field
   - Should see request to `/api/reference/petugas.php?q=...`
   - Response should have `{"success": true, "data": [...]}`

### Issue: Fields don't populate when clicking suggestion
**Solution:**
1. Check that inputs have `id` attributes
2. Check that `bindPetugasAutoFill()` is called after adding row
3. Add logging: Edit row's bindPetugasAutoFill call to log

### Issue: Dropdown appears in wrong position
**Solution:**
This is fixed with position: fixed. If still happening:
1. Check if Bootstrap/CSS conflicts
2. Verify no other CSS uses z-index: 9999+
3. Check browser console for CSS errors

## Architecture Overview

```
petugasAutofill.js (Centralized Module)
├─ injectCSS() → Adds dropdown styling (position: fixed)
├─ loadMasterPegawai() → Fetches from /api/reference/petugas.php
├─ filterPegawai() → Local filtering
├─ bindRow(namaInput, nipInput) → Main entry point
│  ├─ Prevents double-binding
│  ├─ Adds input/change/blur/focus listeners
│  ├─ Shows dropdown with suggestions
│  └─ Auto-fills NIP when nama matches exactly (and vice versa)
└─ showDropdown/hideDropdown → Manages dropdown visibility

All 7 Pages:
├─ Load petugasAutofill.js script
├─ Define minimal bindPetugasAutoFill(row) wrapper
├─ Call PetugasAutofill.bindRow(namaInput, nipInput)
└─ Initialize rows on page load / when adding new rows
```

## Key Features

✅ **Bidirectional Autofill**
- Type nama → NIP auto-fills on exact match
- Type NIP → Nama auto-fills on exact match

✅ **Suggestions Dropdown**
- Shows up to 15 suggestions
- Position: fixed (doesn't break table layout)
- Format: "Nama (NIP: xxxxx)"

✅ **Event Handlers**
- Input: Shows suggestions (debounced 300ms)
- Change: Triggers autofill
- Blur: Closes dropdown (200ms delay)
- Focus: Reopens dropdown if field has value
- Escape: Closes dropdown
- Outside click: Closes dropdown

✅ **Works Everywhere**
- Form pages for WRS, Seismograph, Accelerograph
- Combined form with all 3 types
- Edit/draft pages with pre-filled data
- Mobile responsive (fixed positioning)

## Next Steps

1. Test each of 4 scenarios above
2. Report any issues with specific page/steps
3. Check browser console for errors
4. Provide details about what doesn't work (name/NIP, suggestions yes/no, auto-fill yes/no, etc)

If all tests pass: ✅ **FEATURE COMPLETE** - Ready for production use
