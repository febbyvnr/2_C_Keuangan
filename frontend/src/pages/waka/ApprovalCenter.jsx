import { useState } from "react";
import "../../styles/waka/ApprovalCenter.css";

import FPDPage from "./approve/FPDPengajuanDanaPage";
import RKTPage from "./approve/RKTPage";
import RKAPage from "./approve/RKAPage";
import EvaluasiLPJPage from "./approve/EvaluasiLPJPage";
import PencairanDanaPage from "./approve/PencairanDanaPage";

export default function ApprovalCenter() {
    const [activeTab, setActiveTab] = useState("rkt");
    const [fpdHasPending, setFpdHasPending] = useState(false);
    const [evalHasPending, setEvalHasPending] = useState(false);
    const [rktHasPending, setRktHasPending] = useState(false);
    const [rkaHasPending, setRkaHasPending] = useState(false);
    const [cairHasPending, setCairHasPending] = useState(false);
    const renderContent = () => {
        switch (activeTab) {
            case "rkt":
                return <RKTPage setHasPending={setRktHasPending} />;
            case "fpd":
                return <FPDPage setHasPending={setFpdHasPending} />;
            case "rka":
                return <RKAPage setHasPending={setRkaHasPending} />;
            case "lpj":
                return <EvaluasiLPJPage setHasPending={setEvalHasPending} />;
            case "pencairan":
                return <PencairanDanaPage setHasPending={setCairHasPending} />;
            default:
                return <RKTPage setHasPending={setRktHasPending} />;
        }
    };

    return (
        <div className="app-container">
            <h2>Approval Center</h2>
            <div className="tab-header">
                <button className={activeTab === "rkt" ? "active" : ""} onClick={() => setActiveTab("rkt")} >
                    RKT{rktHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
                <button className={activeTab === "rka" ? "active" : ""} onClick={() => setActiveTab("rka")} >
                    RKA{rkaHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
                <button className={activeTab === "fpd" ? "active" : ""} onClick={() => setActiveTab("fpd")}>
                    FPD Pengajuan Dana{fpdHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
                <button className={activeTab === "pencairan" ? "active" : ""} onClick={() => setActiveTab("pencairan")} >
                    Pencairan Dana{cairHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
                <button className={activeTab === "lpj" ? "active" : ""} onClick={() => setActiveTab("lpj")} >
                    Evaluasi LPJ{evalHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
            </div>
            <div className="tab-content">
                {renderContent()}
            </div>
        </div>
    );
}