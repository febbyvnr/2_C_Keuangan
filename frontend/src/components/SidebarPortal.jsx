import React from 'react';

const SidebarPortal = ({ roleTitle, roleSubtitle, userName, userRole, activeMenu, menus }) => {
  return (
    <div className="w-60 bg-white h-screen border-r border-gray-200 flex flex-col fixed left-0 top-0">

      {/* HEADER */}
      <div className="px-3 py-2 flex items-center gap-2 border-b border-gray-100">
        <div className="w-7 h-7 bg-blue-800 rounded-md text-white flex items-center justify-center text-[10px] font-bold">
          SMK
        </div>
        <div className="leading-tight">
          <h2 className="text-[12px] font-semibold text-gray-900">
            {roleTitle}
          </h2>
          <p className="text-[10px] text-gray-500">
            {roleSubtitle}
          </p>
        </div>
      </div>

      {/* MENU */}
      <nav className="flex-1 px-2 py-3 space-y-1">
        {menus.map((menu) => (
          <div
            key={menu.id}
            className={`px-3 py-2 text-[12px] rounded-md cursor-pointer ${
              activeMenu === menu.id
                ? 'bg-blue-50 text-blue-600 font-semibold border-l-4 border-blue-500'
                : 'text-gray-600 hover:bg-gray-50'
            }`}
          >
            {menu.label}
          </div>
        ))}
      </nav>

      {/* PROFILE (RAPET) */}
      <div className="px-3 py-2 border-t border-gray-100">
        <div className="flex items-center gap-2">

          <div className="w-6 h-6 rounded-full bg-blue-700 text-white flex items-center justify-center text-[9px] font-semibold">
            {userName.substring(0, 2).toUpperCase()}
          </div>

          <div className="leading-tight -space-y-[2px]">
            <p className="text-[11px] font-semibold text-gray-900">
              {userName}
            </p>
            <p className="text-[9px] text-gray-400">
              {userRole}
            </p>
          </div>

        </div>
      </div>

    </div>
  );
};

export default SidebarPortal;