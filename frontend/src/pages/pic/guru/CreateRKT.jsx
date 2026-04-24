import { useEffect, useRef, useState } from "react";
import { useNavigate } from "react-router-dom";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/CreateRKT.css";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";

const createEmptyForm = () => ({
  ID_TA_ANGGARAN: "",
  ID_UNIT: "",
  ID_TAN: "",
  ID_MASTER_COA: "",
  ID_KEGIATAN: "",
  NOMINAL: "",
  INDIKATOR: "",
  SASARAN: "",
  WAKTU_AWAL: "",
  WAKTU_AKHIR: "",
  KELUARAN_PROGKER: "",
  PROGRAM_KERJA: "",
});

const extractCollection = (payload) => {
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  return [];
};

const normalizeUnitLabel = (item) =>
  item?.NAMA_UNIT ??
  item?.DESKRIPSI_UNIT ??
  item?.UNIT ??
  item?.DESKRIPSI ??
  item?.NAMA ??
  `Unit ${item?.ID_UNIT ?? ""}`.trim();

const normalizeTahunAnggaranLabel = (item) =>
  item?.DESKRIPSI_TAHUN_ANGGARAN ??
  item?.label ??
  `TA ${item?.ID_TA_ANGGARAN ?? ""}`.trim();

const normalizeTanLabel = (item) =>
  item?.DESKRIPSI_TAN ?? item?.TAHUN ?? `TAN ${item?.ID_TAN ?? ""}`.trim();

const normalizeCoaLabel = (item) =>
  [item?.KODE_COA, item?.DESKRIPSI_COA].filter(Boolean).join(" - ") ||
  `COA ${item?.ID_MASTER_COA ?? ""}`;

const normalizeKegiatanItems = (items) => {
  const result = [];

  const pushItem = (item) => {
    if (!item || result.some((current) => current.ID_KEGIATAN === item.ID_KEGIATAN)) {
      return;
    }
    result.push(item);
  };

  items.forEach((item) => {
    pushItem(item);
    (item.children || []).forEach(pushItem);
  });

  return result;
};

async function fetchJson(url, options = {}) {
  const response = await fetch(url, options);
  const json = await response.json();

  if (!response.ok || json?.success === false) {
    throw new Error(
      json?.message ||
        json?.error ||
        Object.values(json?.errors || {})?.flat?.()?.[0] ||
        "Terjadi kesalahan pada server"
    );
  }

  return json;
}

export default function CreateRKT() {
  const navigate = useNavigate();

  const [unitOptions, setUnitOptions] = useState([]);
  const [tahunAnggaranOptions, setTahunAnggaranOptions] = useState([]);
  const [tanOptions, setTanOptions] = useState([]);
  const [coaOptions, setCoaOptions] = useState([]);
  const [kegiatanOptions, setKegiatanOptions] = useState([]);

  const [form, setForm] = useState(createEmptyForm());
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  const waktuAwalRef = useRef(null);
  const waktuAkhirRef = useRef(null);

  const loadMasterData = async () => {
    setLoading(true);
    setError("");

    try {
      const [unitJson, taJson, tanJson, coaJson, kegiatanJson] =
        await Promise.all([
          fetchJson(`${API_BASE_URL}/unit`),
          fetchJson(`${API_BASE_URL}/tahun-anggaran`),
          fetchJson(`${API_BASE_URL}/ref-tan`),
          fetchJson(`${API_BASE_URL}/coa`),
          fetchJson(`${API_BASE_URL}/kegiatan`),
        ]);

      const nextUnits = extractCollection(unitJson);
      const nextTa = extractCollection(taJson);
      const nextTan = extractCollection(tanJson);
      const nextCoa = extractCollection(coaJson);
      const nextKegiatan = normalizeKegiatanItems(extractCollection(kegiatanJson));

      setUnitOptions(nextUnits);
      setTahunAnggaranOptions(nextTa);
      setTanOptions(nextTan);
      setCoaOptions(nextCoa);
      setKegiatanOptions(nextKegiatan);

      setForm((current) => ({
        ...current,
        ID_TA_ANGGARAN:
          current.ID_TA_ANGGARAN ||
          String(
            nextTa.find((item) => item.IS_CURRENT)?.ID_TA_ANGGARAN ??
              nextTa[0]?.ID_TA_ANGGARAN ??
              ""
          ),
        ID_TAN:
          current.ID_TAN ||
          String(
            nextTan.find((item) => item.IS_CURRENT)?.ID_TAN ??
              nextTan[0]?.ID_TAN ??
              ""
          ),
        ID_UNIT: current.ID_UNIT || String(nextUnits[0]?.ID_UNIT ?? ""),
        ID_MASTER_COA:
          current.ID_MASTER_COA || String(nextCoa[0]?.ID_MASTER_COA ?? ""),
        ID_KEGIATAN:
          current.ID_KEGIATAN || String(nextKegiatan[0]?.ID_KEGIATAN ?? ""),
      }));
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadMasterData();
  }, []);

  const resetForm = () => {
    setMessage("");
    setError("");

    setForm((current) => ({
      ...createEmptyForm(),
      ID_TA_ANGGARAN: current.ID_TA_ANGGARAN,
      ID_UNIT: current.ID_UNIT,
      ID_TAN: current.ID_TAN,
      ID_MASTER_COA: current.ID_MASTER_COA,
      ID_KEGIATAN: current.ID_KEGIATAN,
    }));
  };

  const handleChange = (event) => {
    const { name, value } = event.target;
    setForm((current) => ({ ...current, [name]: value }));
  };

  const openDatePicker = (inputRef) => {
    if (!inputRef.current) return;

    inputRef.current.focus();

    if (typeof inputRef.current.showPicker === "function") {
      inputRef.current.showPicker();
    } else {
      inputRef.current.click();
    }
  };

  const handleSubmit = async (event) => {
    event.preventDefault();

    const user = JSON.parse(localStorage.getItem("user") || "{}");

    if (!user.NIP_KARYAWAN) {
      setError("Data user login tidak ditemukan. Silakan login ulang.");
      return;
    }

    setSubmitting(true);
    setMessage("");
    setError("");

    const payload = {
      ...form,
      ID_TA_ANGGARAN: Number(form.ID_TA_ANGGARAN),
      ID_UNIT: Number(form.ID_UNIT),
      ID_TAN: form.ID_TAN ? Number(form.ID_TAN) : null,
      ID_MASTER_COA: Number(form.ID_MASTER_COA),
      ID_KEGIATAN: Number(form.ID_KEGIATAN),
      NOMINAL: Number(form.NOMINAL),
      NIP_PENANGGUNG_JAWAB: user.NIP_KARYAWAN,
      NIP_VALIDATOR_PROGKER: null,
    };

    try {
      await fetchJson(`${API_BASE_URL}/rkt/store`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      setMessage("RKT berhasil ditambahkan.");
      navigate("/pic/guru/rkt");
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="create-rkt-shell">
      <SidebarPic />

      <main className="create-rkt-main">
        <section className="create-rkt-card">
          <div className="create-rkt-card-head">
            <div>
              <h2>Tambah RKT</h2>
              <p>Lengkapi data program kerja. Status otomatis menjadi diajukan.</p>
            </div>

            <div className="create-rkt-actions-top">
              <button
                type="button"
                className="create-rkt-button ghost"
                onClick={() => navigate("/pic/guru/rkt")}
              >
                Kembali
              </button>

              <button
                type="button"
                className="create-rkt-button secondary"
                onClick={loadMasterData}
              >
                Refresh
              </button>
            </div>
          </div>

          {message ? (
            <div className="create-rkt-feedback success">{message}</div>
          ) : null}

          {error ? (
            <div className="create-rkt-feedback error">{error}</div>
          ) : null}

          {loading ? (
            <div className="create-rkt-empty">Memuat data form...</div>
          ) : (
            <form className="create-rkt-form" onSubmit={handleSubmit}>
              <div className="create-rkt-grid">
                <label className="create-rkt-field">
                  <span>Tahun Anggaran</span>
                  <select
                    name="ID_TA_ANGGARAN"
                    value={form.ID_TA_ANGGARAN}
                    onChange={handleChange}
                    required
                  >
                    <option value="">Pilih tahun anggaran</option>
                    {tahunAnggaranOptions.map((item) => (
                      <option
                        key={item.ID_TA_ANGGARAN}
                        value={item.ID_TA_ANGGARAN}
                      >
                        {normalizeTahunAnggaranLabel(item)}
                      </option>
                    ))}
                  </select>
                </label>

                <label className="create-rkt-field">
                  <span>Unit</span>
                  <select
                    name="ID_UNIT"
                    value={form.ID_UNIT}
                    onChange={handleChange}
                    required
                  >
                    <option value="">Pilih unit</option>
                    {unitOptions.map((item) => (
                      <option key={item.ID_UNIT} value={item.ID_UNIT}>
                        {normalizeUnitLabel(item)}
                      </option>
                    ))}
                  </select>
                </label>

                <label className="create-rkt-field">
                  <span>TAN</span>
                  <select
                    name="ID_TAN"
                    value={form.ID_TAN}
                    onChange={handleChange}
                  >
                    <option value="">Pilih TAN</option>
                    {tanOptions.map((item) => (
                      <option key={item.ID_TAN} value={item.ID_TAN}>
                        {normalizeTanLabel(item)}
                      </option>
                    ))}
                  </select>
                </label>

                <label className="create-rkt-field">
                  <span>COA</span>
                  <select
                    name="ID_MASTER_COA"
                    value={form.ID_MASTER_COA}
                    onChange={handleChange}
                    required
                  >
                    <option value="">Pilih COA</option>
                    {coaOptions.map((item) => (
                      <option
                        key={item.ID_MASTER_COA}
                        value={item.ID_MASTER_COA}
                      >
                        {normalizeCoaLabel(item)}
                      </option>
                    ))}
                  </select>
                </label>

                <label className="create-rkt-field create-rkt-field-full">
                  <span>Kegiatan</span>
                  <select
                    name="ID_KEGIATAN"
                    value={form.ID_KEGIATAN}
                    onChange={handleChange}
                    required
                  >
                    <option value="">Pilih kegiatan</option>
                    {kegiatanOptions.map((item) => (
                      <option key={item.ID_KEGIATAN} value={item.ID_KEGIATAN}>
                        {item.DESKRIPSI_KEGIATAN}
                      </option>
                    ))}
                  </select>
                </label>

                <label className="create-rkt-field create-rkt-field-full">
                  <span>Program Kerja</span>
                  <input
                    type="text"
                    name="PROGRAM_KERJA"
                    value={form.PROGRAM_KERJA}
                    onChange={handleChange}
                    required
                  />
                </label>

                <label className="create-rkt-field">
                  <span>Nominal</span>
                  <input
                    type="number"
                    min="0"
                    name="NOMINAL"
                    value={form.NOMINAL}
                    onChange={handleChange}
                    required
                  />
                </label>

                <label className="create-rkt-field">
                  <span>Waktu Awal</span>
                  <div className="create-rkt-date-input">
                    <input
                      ref={waktuAwalRef}
                      type="date"
                      name="WAKTU_AWAL"
                      value={form.WAKTU_AWAL}
                      onChange={handleChange}
                      required
                    />
                    <button
                      type="button"
                      className="create-rkt-date-trigger"
                      onClick={() => openDatePicker(waktuAwalRef)}
                      aria-label="Buka kalender waktu awal"
                    >
                      <i className="bi bi-calendar3"></i>
                    </button>
                  </div>
                </label>

                <label className="create-rkt-field">
                  <span>Waktu Akhir</span>
                  <div className="create-rkt-date-input">
                    <input
                      ref={waktuAkhirRef}
                      type="date"
                      name="WAKTU_AKHIR"
                      value={form.WAKTU_AKHIR}
                      onChange={handleChange}
                      required
                    />
                    <button
                      type="button"
                      className="create-rkt-date-trigger"
                      onClick={() => openDatePicker(waktuAkhirRef)}
                      aria-label="Buka kalender waktu akhir"
                    >
                      <i className="bi bi-calendar3"></i>
                    </button>
                  </div>
                </label>

                <label className="create-rkt-field">
                  <span>Indikator</span>
                  <input
                    type="text"
                    name="INDIKATOR"
                    value={form.INDIKATOR}
                    onChange={handleChange}
                    required
                  />
                </label>

                <label className="create-rkt-field">
                  <span>Sasaran</span>
                  <input
                    type="text"
                    name="SASARAN"
                    value={form.SASARAN}
                    onChange={handleChange}
                    required
                  />
                </label>

                <label className="create-rkt-field create-rkt-field-full">
                  <span>Keluaran Program Kerja</span>
                  <textarea
                    name="KELUARAN_PROGKER"
                    value={form.KELUARAN_PROGKER}
                    onChange={handleChange}
                    rows="3"
                    required
                  />
                </label>
              </div>

              <div className="create-rkt-submit">
                <button
                  type="button"
                  className="create-rkt-button secondary"
                  onClick={resetForm}
                >
                  Reset
                </button>

                <button
                  type="submit"
                  className="create-rkt-button primary"
                  disabled={submitting}
                >
                  {submitting ? "Menyimpan..." : "Simpan RKT"}
                </button>
              </div>
            </form>
          )}
        </section>
      </main>
    </div>
  );
}