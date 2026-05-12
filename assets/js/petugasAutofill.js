/**
 * PETUGAS NAMA/NIP AUTOFILL SYSTEM - BIDIRECTIONAL
 * 
 * Fitur:
 * - Autofill dua arah: Nama <-> NIP
 * - Datalist untuk dropdown suggestions (HTML5 native)
 * - Hindari infinite loop dengan sync state flag
 * - Validasi sinkronisasi nama dan NIP
 * - Search di kedua field (nama dan NIP)
 */

const PetugasAutofill = (() => {
  // ========================================
  // STATE & CACHE
  // ========================================
  
  // Cache master data petugas dari server
  const masterCache = { 
    promise: null, 
    data: null,
    loaded: false
  };

  // Untuk mencegah infinite loop saat autofill
  // Track state autofill (fromNama atau fromNip)
  const syncState = new WeakMap();

  // ========================================
  // LOAD MASTER DATA
  // ========================================

  /**
   * Load master data petugas dari API endpoint
   * Data di-cache untuk performa
   */
  async function loadMasterPegawai() {
    if (masterCache.data) {
      return Promise.resolve(masterCache.data);
    }
    if (masterCache.promise) {
      return masterCache.promise;
    }

    masterCache.promise = (async () => {
      try {
        const response = await fetch(`${BASE_URL}/api/reference/master_petugas_json.php`, {
          method: 'GET',
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        });
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        masterCache.data = Array.isArray(data) ? data : [];
        masterCache.loaded = true;
        
        console.log(`[PetugasAutofill] Loaded ${masterCache.data.length} petugas data`);
        return masterCache.data;
      } catch (error) {
        console.error('[PetugasAutofill] Error loading master pegawai:', error);
        masterCache.data = [];
        masterCache.loaded = true;
        return masterCache.data;
      }
    })();

    return masterCache.promise;
  }

  // ========================================
  // SEARCH & FILTER FUNCTIONS
  // ========================================

  /**
   * Normalize teks untuk perbandingan (lowercase, trim)
   * Untuk search dan exact match
   */
  function normalizeText(value) {
    return (value || '').trim().toLowerCase();
  }

  /**
   * Filter daftar petugas berdasarkan query
   * Search di nama dan NIP sekaligus (partial match)
   */
  function filterPegawai(list, query) {
    const q = normalizeText(query);
    if (!q) return list;
    
    return list.filter(item => {
      const nama = normalizeText(item.nama || '');
      const nip = normalizeText(item.nip || '');
      return nama.includes(q) || nip.includes(q);
    });
  }

  /**
   * Cari exact match untuk nama (untuk autofill)
   */
  function findByNama(list, nama) {
    const normalized = normalizeText(nama);
    return list.find(item => normalizeText(item.nama) === normalized);
  }

  /**
   * Cari exact match untuk NIP (untuk autofill)
   */
  function findByNip(list, nip) {
    const normalized = normalizeText(nip);
    return list.find(item => normalizeText(item.nip) === normalized);
  }

  // ========================================
  // DATALIST MANAGEMENT
  // ========================================

  /**
   * Create atau get datalist element dari DOM
   * Datalist ini akan di-attach ke input via list attribute
   */
  function getOrCreateDatalist(inputId, type) {
    const listId = `list_${type}_${inputId}`;
    let datalist = document.getElementById(listId);
    
    if (!datalist) {
      datalist = document.createElement('datalist');
      datalist.id = listId;
      document.body.appendChild(datalist);
    }
    
    return datalist;
  }

  /**
   * Populate datalist dengan opsi berdasarkan filter
   * Type 'nama' atau 'nip' untuk format yang berbeda
   */
  function populateDatalist(datalist, items, type) {
    // Clear existing options
    datalist.innerHTML = '';
    
    if (!items || items.length === 0) {
      return;
    }

    // Limit to first 50 untuk performa (UI rendering)
    items.slice(0, 50).forEach(item => {
      const option = document.createElement('option');
      
      if (type === 'nama') {
        // Format: "NAMA (NIP: 123456789)"
        option.value = item.nama;
        option.textContent = `${item.nama} (NIP: ${item.nip})`;
      } else {
        // Format: "123456789 - NAMA"
        option.value = item.nip;
        option.textContent = `${item.nip} - ${item.nama}`;
      }
      
      datalist.appendChild(option);
    });
  }

  // ========================================
  // AUTOFILL LOGIC (PREVENT INFINITE LOOP)
  // ========================================

  /**
   * Set sync state untuk mencegah infinite loop
   * Flag set saat autofill dimulai, dilepas setelah user interaksi
   * Contoh: Jika user input Nama -> autofill NIP -> saat NIP change event
   * jangan autofill Nama balik (loop)
   */
  function setSyncState(namaInput, state) {
    syncState.set(namaInput, {
      fromNama: state.fromNama !== undefined ? state.fromNama : false,
      fromNip: state.fromNip !== undefined ? state.fromNip : false,
      lastSync: Date.now()
    });
  }

  /**
   * Get sync state
   */
  function getSyncState(namaInput) {
    return syncState.get(namaInput) || { fromNama: false, fromNip: false, lastSync: 0 };
  }

  /**
   * Clear sync state (saat user manual input)
   * Agar cycle autofill bisa dimulai lagi
   */
  function clearSyncState(namaInput) {
    syncState.delete(namaInput);
  }

  /**
   * Autofill NIP berdasarkan Nama
   * Hanya autofill jika nama exact match ditemukan
   * Set sync state agar tidak ada infinite loop
   */
  async function autofillNipFromNama(namaInput, nipInput, list) {
    const nama = namaInput.value || '';
    if (!nama.trim()) {
      nipInput.value = '';
      return;
    }

    const match = findByNama(list, nama);
    if (match && match.nip) {
      // Mark yang autofill dimulai dari Nama field
      // Jadi saat NIP change event, jangan autofill Nama balik
      setSyncState(namaInput, { fromNama: true, fromNip: false });
      nipInput.value = match.nip;
    } else {
      nipInput.value = '';
    }
  }

  /**
   * Autofill Nama berdasarkan NIP
   * Hanya autofill jika NIP exact match ditemukan
   * Set sync state agar tidak ada infinite loop
   */
  async function autofillNamaFromNip(namaInput, nipInput, list) {
    const nip = nipInput.value || '';
    if (!nip.trim()) {
      namaInput.value = '';
      return;
    }

    const match = findByNip(list, nip);
    if (match && match.nama) {
      // Mark yang autofill dimulai dari NIP field
      // Jadi saat Nama change event, jangan autofill NIP balik
      setSyncState(namaInput, { fromNama: false, fromNip: true });
      namaInput.value = match.nama;
    } else {
      namaInput.value = '';
    }
  }

  // ========================================
  // BIND ROW (MAIN ENTRY POINT)
  // ========================================

  /**
   * MAIN FUNCTION: Bind autofill ke pair input Nama dan NIP
   * 
   * Cara kerja:
   * 1. User input di field Nama -> datalist muncul + autofill NIP jika match
   * 2. User pilih dari datalist -> Nama + NIP terisi, sync state set fromNama=true
   * 3. Saat NIP change event triggered, cek sync state:
   *    - Jika fromNama=true, skip autofill Nama (hindari loop)
   *    - Clear sync state, agar siap untuk cycle berikutnya
   * 4. Begitu juga sebaliknya untuk NIP input
   * 
   * @param {HTMLElement} namaInput - Input field nama
   * @param {HTMLElement} nipInput - Input field NIP
   * @param {Object} options - Optional config
   */
  function bindRow(namaInput, nipInput, options = {}) {
    if (!namaInput || !nipInput) {
      console.warn('[PetugasAutofill] bindRow: missing inputs', { namaInput, nipInput });
      return;
    }

    // Prevent double binding
    if (namaInput.dataset.petugasAutofillBound === 'true') {
      return;
    }
    namaInput.dataset.petugasAutofillBound = 'true';

    const inputId = namaInput.id || nipInput.id || `row_${Math.random().toString(36).substr(2, 9)}`;
    const callback = options.onSelect || (() => {});

    // Load master data dan set up event listeners
    loadMasterPegawai().then(list => {
      if (!list || list.length === 0) {
        console.warn('[PetugasAutofill] No master data loaded');
        return;
      }

      // =============================================
      // NAMA INPUT: Show suggestions & autofill NIP
      // =============================================
      
      /**
       * Event: user mengetik di field Nama
       * - Populate datalist dengan hasil filter
       * - Clear sync state (user sedang input manual)
       */
      namaInput.addEventListener('input', async (e) => {
        clearSyncState(namaInput);
        
        const query = namaInput.value || '';
        const filtered = filterPegawai(list, query);

        // Populate datalist dengan filtered results
        const datalist = getOrCreateDatalist(inputId, 'nama');
        populateDatalist(datalist, filtered, 'nama');

        // Update input list attribute kalau belum
        if (!namaInput.list) {
          namaInput.setAttribute('list', datalist.id);
        }
      });

      /**
       * Event: user selesai input/select di field Nama
       * - Check sync state (apakah baru saja dari autofill NIP?)
       * - Jika ya, skip (hindari infinite loop)
       * - Jika tidak, lakukan autofill NIP
       */
      namaInput.addEventListener('change', async (e) => {
        const state = getSyncState(namaInput);
        
        // Kalau sync state fromNip=true, berarti input Nama baru saja dari
        // autofill (user tidak sengaja input), skip autofill NIP
        if (state.fromNip) {
          clearSyncState(namaInput);
          return;
        }

        // Autofill NIP jika nama exact match
        await autofillNipFromNama(namaInput, nipInput, list);
        callback({ nama: namaInput.value, nip: nipInput.value });
      });

      /**
       * Event: focus di field Nama
       * Jika kosong, tampilkan semua pilihan
       */
      namaInput.addEventListener('focus', async (e) => {
        if (!namaInput.value) {
          const datalist = getOrCreateDatalist(inputId, 'nama');
          populateDatalist(datalist, list, 'nama');
        }
      });

      // =============================================
      // NIP INPUT: Show suggestions & autofill Nama
      // =============================================
      
      /**
       * Event: user mengetik di field NIP
       * - Populate datalist dengan hasil filter
       * - Clear sync state (user sedang input manual)
       */
      nipInput.addEventListener('input', async (e) => {
        clearSyncState(namaInput);
        
        const query = nipInput.value || '';
        const filtered = filterPegawai(list, query);

        // Populate datalist dengan filtered results
        const datalist = getOrCreateDatalist(inputId, 'nip');
        populateDatalist(datalist, filtered, 'nip');

        // Update input list attribute kalau belum
        if (!nipInput.list) {
          nipInput.setAttribute('list', datalist.id);
        }
      });

      /**
       * Event: user selesai input/select di field NIP
       * - Check sync state (apakah baru saja dari autofill Nama?)
       * - Jika ya, skip (hindari infinite loop)
       * - Jika tidak, lakukan autofill Nama
       */
      nipInput.addEventListener('change', async (e) => {
        const state = getSyncState(namaInput);
        
        // Kalau sync state fromNama=true, berarti input NIP baru saja dari
        // autofill (user tidak sengaja input), skip autofill Nama
        if (state.fromNama) {
          clearSyncState(namaInput);
          return;
        }

        // Autofill Nama jika NIP exact match
        await autofillNamaFromNip(namaInput, nipInput, list);
        callback({ nama: namaInput.value, nip: nipInput.value });
      });

      /**
       * Event: focus di field NIP
       * Jika kosong, tampilkan semua pilihan
       */
      nipInput.addEventListener('focus', async (e) => {
        if (!nipInput.value) {
          const datalist = getOrCreateDatalist(inputId, 'nip');
          populateDatalist(datalist, list, 'nip');
        }
      });

      // =============================================
      // INITIAL VALIDATION
      // =============================================
      
      // Jika sudah ada value saat binding, validate & autofill
      if (namaInput.value && !nipInput.value) {
        autofillNipFromNama(namaInput, nipInput, list);
      } else if (nipInput.value && !namaInput.value) {
        autofillNamaFromNip(namaInput, nipInput, list);
      }

      console.log(`[PetugasAutofill] Bound inputs: ${namaInput.id || 'unnamed'} <-> ${nipInput.id || 'unnamed'}`);
    });
  }

  // ========================================
  // PUBLIC API
  // ========================================

  return {
    // Main entry point untuk bind input pair
    bindRow,
    
    // Utility functions
    loadMasterPegawai,
    normalizeText,
    filterPegawai,
    findByNama,
    findByNip,
    
    // For debugging
    getState: () => ({
      loaded: masterCache.loaded,
      dataCount: masterCache.data ? masterCache.data.length : 0,
      data: masterCache.data
    })
  };
})();
