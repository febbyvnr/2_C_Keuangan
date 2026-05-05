import { useState, useEffect } from "react";
import "../../styles/waka/ApprovalCenter.css";

import FPDPage from "./approve/FPDPengajuanDanaPage";

export default function ApprovalCenter() {
    const [fpdHasPending, setFpdHasPending] = useState(false);

    return (
        <div className="app-container">
            <h2>
                FPD Pengajuan Dana{" "}
                {/* {fpdHasPending && (<i className="bi bi-exclamation-circle-fill tab-warning"></i>)} */}
            </h2>
            <div className="tab-content">
                <FPDPage setHasPending={setFpdHasPending} />
            </div>
        </div>
    );
}