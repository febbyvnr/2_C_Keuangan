import { useState, useEffect } from "react";
import "../../styles/waka/ApprovalCenter.css";

import FPDPage from "./approve/FPDPengajuanDanaPage";
import RKTPage from "./approve/RKTPage";
import EvaluasiRKTPage from "./approve/EvaluasiRKTPage";

export default function ApprovalCenter() {
    const [activeTab, setActiveTab] = useState("rkt");
    const [fpdHasPending, setFpdHasPending] = useState(false);
    const [rktHasPending, setRktHasPending] = useState(false);
    const [rkaHasPending, setRkaHasPending] = useState(false);
    const [evalHasPending, setEvalHasPending] = useState(false);

    const renderContent = () => {
        switch (activeTab) {
            case "rkt":
                return <RKTPage setHasPending={setRktHasPending} />;
            case "fpd":
                return <FPDPage setHasPending={setFpdHasPending} />;
            case "lpj":
                return <EvaluasiRKTPage setHasPending={setEvalHasPending} />;
            default:
                return <RKTPage setHasPending={setRktHasPending} />;
        }
    };

    return (
        <div className="app-container">
            <h2>Approval Center{" "}</h2>
            <div className="tab-header">
                <button className={activeTab === "rkt" ? "active" : ""} onClick={() => setActiveTab("rkt")} >
                    RKT{rktHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
                <button className={activeTab === "fpd" ? "active" : ""} onClick={() => setActiveTab("fpd")}>
                    FPD{fpdHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
                <button className={activeTab === "lpj" ? "active" : ""} onClick={() => setActiveTab("lpj")} >
                    Evaluasi RKT{evalHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)}
                </button>
            </div>
            <div className="tab-content">
                {renderContent()}
            </div>
        </div>
    );
}