import Sidebar from "../components/Sidebar"

export default function MainLayout({children}) {
    return (
        <div className="d-flex">
            <Sidebar/>
            <div className="flex-grow-1 p-4">
                {children}
            </div>
        </div>
    )
}