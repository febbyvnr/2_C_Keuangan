import { useEffect, useRef, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/CreateRKT.css";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";

const createEmptyForm = () => ({
  ID_TA_ANGGARAN: "",
  // ID_UNIT: "",
  ID_TAN: "",
  ID_MASTER_COA: "",
  ID_KEGIATAN: "",
  TOTAL_PROGKER: "",
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

const extractObject = (payload) => {
  if (payload?.data?.data) return payload.data.data;
  if (payload?.data) return payload.data;
  return payload;
};

const normalizeDate = (value) => {
  if (!value) return "";
  return String(value).slice(0, 10);
};

// const normalizeUnitLabel = (item) =>
//   item?.NAMA_UNIT ??
//   item?.DESKRIPSI_UNIT ??
//   item?.UNIT ??
//   item?.DESKRIPSI ??
//   item?.NAMA ??
//   `Unit ${item?.ID_UNIT ?? ""}`.trim();

const normalizeTahunAnggaranLabel = (item) =>
  item?.DESKRIPSI_TAHUN_ANGGARAN ??
  item?.label ??
  item?.TAHUN_ANGGARAN ??
  `TA ${item?.ID_TA_ANGGARAN ?? ""}`.trim();

const normalizeTanLabel = (item) =>
  item?.DESKRIPSI_TAN ?? item?.TAHUN ?? `TAN ${item?.ID_TAN ?? ""}`.trim();

const normalizeCoaLabel = (item) =>
  [item?.KODE_COA, item?.DESKRIPSI_COA].filter(Boolean).join(" - ") ||
  `COA ${item?.ID_MASTER_COA ?? ""}`;

const normalizeKegiatanItems = (items) => {
  const result = [];

  const pushItem = (item) => {
    if (
      !item ||
      result.some((current) => current.ID_KEGIATAN === item.ID_KEGIATAN)
    ) {
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
  const text = await response.text();
  const json = text ? JSON.parse(text) : {};

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
  const { id } = useParams();

  const isEditMode = Boolean(id);

  // const [unitOptions, setUnitOptions] = useState([]);
  const [tahunAnggaranOptions, setTahunAnggaranOptions] = useState([]);
  const [tanOptions, setTanOptions] = useState([]);
  const [coaOptions, setCoaOptions] = useState([]);
  const [kegiatanOptions, setKegiatanOptions] = useState([]);

  const [form, setForm] = useState(createEmptyForm());
  const [loading, setLoading] = useState(true);
  const [submittingAction, setSubmittingAction] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [catatanReview, setCatatanReview] = useState("");

  const waktuAwalRef = useRef(null);
  const waktuAkhirRef = useRef(null);

  const userLogin = JSON.parse(localStorage.getItem("user") || "{}");

  const penanggungJawabLabel = [
    userLogin.NIP_KARYAWAN,
    userLogin.NAMA_KARYAWAN || userLogin.nama_karyawan || userLogin.name,
  ]
    .filter(Boolean)
    .join(" - ");

  const loadMasterData = async () => {
    // const [unitJson, taJson, tanJson, coaJson, kegiatanJson] =
    //   await Promise.all([
    //     fetchJson(`${API_BASE_URL}/unit`),
    //     fetchJson(`${API_BASE_URL}/tahun-anggaran`),
    //     fetchJson(`${API_BASE_URL}/ref-tan`),
    //     fetchJson(`${API_BASE_URL}/coa`),
    //     fetchJson(`${API_BASE_URL}/kegiatan`),
    //   ]);
    const [taJson, tanJson, coaJson, kegiatanJson] =
      await Promise.all([
        fetchJson(`${API_BASE_URL}/tahun-anggaran`),
        fetchJson(`${API_BASE_URL}/ref-tan`),
        fetchJson(`${API_BASE_URL}/coa`),
        fetchJson(`${API_BASE_URL}/kegiatan`),
      ]);

      setTahunAnggaranOptions(extractCollection(taJson));
      setTanOptions(extractCollection(tanJson));
      setCoaOptions(extractCollection(coaJson));
      setKegiatanOptions(normalizeKegiatanItems(extractCollection(kegiatanJson)));

    // setUnitOptions(extractCollection(unitJson));
    // setTahunAnggaranOptions(extractCollection(taJson));
    // setTanOptions(extractCollection(tanJson));
    // setCoaOptions(extractCollection(coaJson));
    // setKegiatanOptions(normalizeKegiatanItems(extractCollection(kegiatanJson)));
  };

  const loadRktDetail = async () => {
    const json = await fetchJson(`${API_BASE_URL}/rkt/${id}`);
    const data = extractObject(json);

    const trPmList = data?.trPm || data?.tr_pm || [];
    const reviewNote = [...trPmList]
      .reverse()
      .find((item) => {
        const note = String(item?.DESKRIPSI_TR_PM || "").toLowerCase();

        return (
          note.includes("revisi") ||
          note.includes("ditolak") ||
          note.includes("tolak")
        );
      })?.DESKRIPSI_TR_PM;

    setCatatanReview(reviewNote || "" );

    setForm({
      ID_TA_ANGGARAN: String(data?.ID_TA_ANGGARAN ?? ""),
      // ID_UNIT: String(data?.ID_UNIT ?? ""),
      ID_TAN: data?.ID_TAN ? String(data.ID_TAN) : "",
      ID_MASTER_COA: String(data?.ID_MASTER_COA ?? ""),
      ID_KEGIATAN: String(data?.ID_KEGIATAN ?? ""),
      TOTAL_PROGKER: String(data?.TOTAL_PROGKER ?? ""),
      INDIKATOR: data?.INDIKATOR ?? "",
      SASARAN: data?.SASARAN ?? "",
      WAKTU_AWAL: normalizeDate(data?.WAKTU_AWAL),
      WAKTU_AKHIR: normalizeDate(data?.WAKTU_AKHIR),
      KELUARAN_PROGKER: data?.KELUARAN_PROGKER ?? "",
      PROGRAM_KERJA: data?.PROGRAM_KERJA ?? "",
    });
  };

  const initializePage = async () => {
    setLoading(true);
    setError("");
    setMessage("");

    try {
      await loadMasterData();

      if (isEditMode) {
        await loadRktDetail();
      } else {
        setForm(createEmptyForm());
        setCatatanReview("");
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    initializePage();
  }, [id]);

  const resetForm = () => {
    setMessage("");
    setError("");

    if (isEditMode) {
      loadRktDetail();
      return;
    }

    setForm(createEmptyForm());
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

  const handleSubmit = async (event, aksi = "AJUKAN") => {
    event.preventDefault();

    const user = JSON.parse(localStorage.getItem("user") || "{}");

    if (!user.NIP_KARYAWAN) {
      setError("Data user login tidak ditemukan. Silakan login ulang.");
      return;
    }

    setSubmittingAction(aksi);
    setMessage("");
    setError("");

    const payload = {
      ...form,
      ID_TA_ANGGARAN: Number(form.ID_TA_ANGGARAN),
      // ID_UNIT: Number(form.ID_UNIT),
      ID_TAN: form.ID_TAN ? Number(form.ID_TAN) : null,
      ID_MASTER_COA: Number(form.ID_MASTER_COA),
      ID_KEGIATAN: Number(form.ID_KEGIATAN),
      TOTAL_PROGKER: Number(form.TOTAL_PROGKER),
      NIP_PENANGGUNG_JAWAB: user.NIP_KARYAWAN,
      NIP_LOGIN: user.NIP_KARYAWAN,
      AKSI: aksi,
    };

    try {
      const url = isEditMode
        ? `${API_BASE_URL}/rkt/update/${id}`
        : `${API_BASE_URL}/rkt/store`;

      const method = isEditMode ? "PUT" : "POST";

      await fetchJson(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      setMessage(
        isEditMode
          ? "RKT berhasil diperbarui."
          : aksi === "DRAFT"
          ? "RKT berhasil disimpan sebagai draft."
          : "RKT berhasil diajukan."
      );

      navigate("/pic/guru/rkt");
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmittingAction("");
    }
  };

  return (
    <div className="create-rkt-shell">
      <SidebarPic />

      <main className="create-rkt-main">
        <section className="create-rkt-card">
          <div className="create-rkt-card-head">
            <div>
              <h2>{isEditMode ? "Edit RKT" : "Tambah RKT"}</h2>
              <p>
                {isEditMode
                  ? "Perbarui data RKT sesuai catatan review."
                  : "Simpan sebagai draft atau ajukan RKT untuk direview."}
              </p>
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
                onClick={initializePage}
                disabled={loading}
              >
                Refresh
              </button>
            </div>
          </div>

          {isEditMode && catatanReview ? (
            <div className="create-rkt-feedback error">
              <strong>Catatan Review:</strong> {catatanReview}
            </div>
          ) : null}

          {message ? (
            <div className="create-rkt-feedback success">{message}</div>
          ) : null}

          {error ? (
            <div className="create-rkt-feedback error">{error}</div>
          ) : null}

          {loading ? (
            <div className="create-rkt-empty">Memuat data form...</div>
          ) : (
            <form
              className="create-rkt-form"
              onSubmit={(event) => handleSubmit(event, "AJUKAN")}
            >
              <div className="create-rkt-master-grid">
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

                {/* <label className="create-rkt-field">
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
                </label> */}

                <label className="create-rkt-field">
                  <span>TAN</span>
                  <select
                    name="ID_TAN"
                    value={form.ID_TAN}
                    onChange={handleChange}
                    required
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
              </div>

              <div className="create-rkt-grid">
                <label className="create-rkt-field">
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

                <label className="create-rkt-field">
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
                  <span>Nominal (Pagu)</span>
                  <input
                    type="number"
                    min="0"
                    name="TOTAL_PROGKER"
                    value={form.TOTAL_PROGKER}
                    onChange={handleChange}
                    required
                  />
                </label>

                <label className="create-rkt-field create-rkt-field-pj">
                  <span>Penanggung Jawab</span>
                  <input
                    type="text"
                    value={penanggungJawabLabel}
                    readOnly
                    placeholder="Data user login tidak ditemukan"
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
                  disabled={Boolean(submittingAction)}
                >
                  Reset
                </button>

                {!isEditMode && (
                  <button
                    type="button"
                    className="create-rkt-button secondary"
                    disabled={Boolean(submittingAction)}
                    onClick={(event) => handleSubmit(event, "DRAFT")}
                  >
                    {submittingAction === "DRAFT"
                      ? "Menyimpan..."
                      : "Simpan Draft"}
                  </button>
                )}

                <button
                  type="submit"
                  className="create-rkt-button primary"
                  disabled={Boolean(submittingAction)}
                >
                  {submittingAction === "AJUKAN"
                    ? isEditMode
                      ? "Menyimpan..."
                      : "Mengajukan..."
                    : isEditMode
                    ? "Simpan Perbaikan"
                    : "Ajukan RKT"}
                </button>

                {!isEditMode && (
                  <p className="create-rkt-hint">
                    Draft bisa diedit kapan saja sebelum diajukan.
                  </p>
                )}
              </div>
            </form>
          )}
        </section>
      </main>
    </div>
  );
}