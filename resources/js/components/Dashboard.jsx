import React, { useEffect, useRef } from 'react';
import {
    BarChart, Bar, AreaChart, Area, LineChart, Line,
    PieChart, Pie, Cell,
    XAxis, YAxis, CartesianGrid, Tooltip, Legend,
    ResponsiveContainer,
} from 'recharts';

// ── Formatting helpers ─────────────────────────────────────────

function formatCurrency(value) {
    if (value >= 1_000_000) return (value / 1_000_000).toFixed(1) + 'M';
    if (value >= 1_000)     return (value / 1_000).toFixed(1) + 'K';
    return value.toFixed(0);
}

function fmt2(value) {
    return parseFloat(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

// ── Custom Tooltip styles ───────────────────────────────────────

const tooltipStyle = {
    backgroundColor: '#fff',
    border         : '1px solid #e2e8f0',
    borderRadius   : '8px',
    fontSize       : '12px',
    boxShadow      : '0 4px 16px rgba(0,0,0,0.08)',
    padding        : '8px 12px',
};

// ── Chart: Revenue bar (last 30 days) ──────────────────────────

function RevenueChart({ data }) {
    if (!data || data.length === 0) {
        return (
            <div style={{ height: '100%', display: 'flex', alignItems: 'center',
                          justifyContent: 'center', color: '#94a3b8', fontSize: 13 }}>
                No revenue data yet.
            </div>
        );
    }

    // Show every 5th label to avoid crowding
    const tickFormatter = (_, index) =>
        index % 5 === 0 ? data[index]?.label || '' : '';

    return (
        <ResponsiveContainer width="100%" height="100%">
            <BarChart data={data} barSize={6}
                      margin={{ top: 4, right: 4, left: -8, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9"
                               vertical={false}/>
                <XAxis dataKey="label" tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       interval="preserveStartEnd"/>
                <YAxis tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       tickFormatter={formatCurrency}/>
                <Tooltip
                    contentStyle={tooltipStyle}
                    formatter={(v) => [fmt2(v), 'Revenue']}
                    cursor={{ fill: '#f8fafc' }}
                />
                <Bar dataKey="revenue" fill="#f59e0b" radius={[3, 3, 0, 0]}/>
            </BarChart>
        </ResponsiveContainer>
    );
}

// ── Chart: Room status donut ───────────────────────────────────

function RoomStatusChart({ data }) {
    if (!data || data.length === 0) {
        return (
            <div style={{ height: '100%', display: 'flex', alignItems: 'center',
                          justifyContent: 'center', color: '#94a3b8', fontSize: 13 }}>
                No room data.
            </div>
        );
    }

    // Filter out zero values
    const filtered = data.filter(d => d.value > 0);

    return (
        <ResponsiveContainer width="100%" height="100%">
            <PieChart>
                <Pie
                    data={filtered}
                    cx="50%"
                    cy="45%"
                    innerRadius={55}
                    outerRadius={85}
                    paddingAngle={3}
                    dataKey="value"
                >
                    {filtered.map((entry, index) => (
                        <Cell key={index} fill={entry.color} strokeWidth={0}/>
                    ))}
                </Pie>
                <Tooltip
                    contentStyle={tooltipStyle}
                    formatter={(v, name) => [v + ' rooms', name]}
                />
                <Legend
                    iconType="circle"
                    iconSize={8}
                    wrapperStyle={{ fontSize: 11, paddingTop: 8 }}
                />
            </PieChart>
        </ResponsiveContainer>
    );
}

// ── Chart: Booking trends (area) ───────────────────────────────

function BookingsChart({ data }) {
    if (!data || data.length === 0) {
        return (
            <div style={{ height: '100%', display: 'flex', alignItems: 'center',
                          justifyContent: 'center', color: '#94a3b8', fontSize: 13 }}>
                No booking data yet.
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={data}
                       margin={{ top: 4, right: 4, left: -8, bottom: 0 }}>
                <defs>
                    <linearGradient id="bookingGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%"  stopColor="#3b82f6" stopOpacity={0.15}/>
                        <stop offset="95%" stopColor="#3b82f6" stopOpacity={0}/>
                    </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9"
                               vertical={false}/>
                <XAxis dataKey="label" tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       interval="preserveStartEnd"/>
                <YAxis tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false} allowDecimals={false}/>
                <Tooltip
                    contentStyle={tooltipStyle}
                    formatter={(v) => [v, 'Bookings']}
                    cursor={{ stroke: '#3b82f6', strokeWidth: 1 }}
                />
                <Area
                    type="monotone"
                    dataKey="count"
                    stroke="#3b82f6"
                    strokeWidth={2}
                    fill="url(#bookingGrad)"
                    dot={false}
                    activeDot={{ r: 4, fill: '#3b82f6', strokeWidth: 0 }}
                />
            </AreaChart>
        </ResponsiveContainer>
    );
}

// ── Chart: Monthly revenue bar ─────────────────────────────────

function MonthlyRevenueChart({ data }) {
    if (!data || data.length === 0) {
        return (
            <div style={{ height: '100%', display: 'flex', alignItems: 'center',
                          justifyContent: 'center', color: '#94a3b8', fontSize: 13 }}>
                No monthly data.
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <BarChart data={data} barSize={20}
                      margin={{ top: 4, right: 4, left: -8, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9"
                               vertical={false}/>
                <XAxis dataKey="month" tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}/>
                <YAxis tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       tickFormatter={formatCurrency}/>
                <Tooltip
                    contentStyle={tooltipStyle}
                    formatter={(v) => [fmt2(v), 'Revenue']}
                    cursor={{ fill: '#f8fafc' }}
                />
                <Bar dataKey="revenue" fill="#10b981" radius={[4, 4, 0, 0]}/>
            </BarChart>
        </ResponsiveContainer>
    );
}

// ── Report: Revenue line chart (for reports page) ──────────────

function ReportRevenueChart({ data }) {
    if (!data || data.length === 0) {
        return (
            <div style={{ height: '100%', display: 'flex', alignItems: 'center',
                          justifyContent: 'center', color: '#94a3b8', fontSize: 13 }}>
                No data for this period.
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <BarChart data={data} barSize={Math.max(4, Math.min(16, 300 / data.length))}
                      margin={{ top: 4, right: 4, left: -8, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9"
                               vertical={false}/>
                <XAxis dataKey="label" tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       interval={Math.floor(data.length / 8)}/>
                <YAxis tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       tickFormatter={formatCurrency}/>
                <Tooltip
                    contentStyle={tooltipStyle}
                    formatter={(v) => [fmt2(v), 'Revenue']}
                    cursor={{ fill: '#f8fafc' }}
                />
                <Bar dataKey="revenue" fill="#10b981" radius={[3, 3, 0, 0]}/>
            </BarChart>
        </ResponsiveContainer>
    );
}

// ── Report: Occupancy line chart ───────────────────────────────

function OccupancyChart({ data }) {
    if (!data || data.length === 0) {
        return (
            <div style={{ height: '100%', display: 'flex', alignItems: 'center',
                          justifyContent: 'center', color: '#94a3b8', fontSize: 13 }}>
                No data for this period.
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <LineChart data={data}
                       margin={{ top: 4, right: 4, left: -8, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9"
                               vertical={false}/>
                <XAxis dataKey="label" tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       interval={Math.floor(data.length / 8)}/>
                <YAxis tick={{ fontSize: 10, fill: '#94a3b8' }}
                       tickLine={false} axisLine={false}
                       domain={[0, 100]}
                       tickFormatter={(v) => v + '%'}/>
                <Tooltip
                    contentStyle={tooltipStyle}
                    formatter={(v) => [v + '%', 'Occupancy']}
                    cursor={{ stroke: '#3b82f6', strokeWidth: 1 }}
                />
                <Line
                    type="monotone"
                    dataKey="rate"
                    stroke="#3b82f6"
                    strokeWidth={2}
                    dot={false}
                    activeDot={{ r: 4, fill: '#3b82f6', strokeWidth: 0 }}
                />
            </LineChart>
        </ResponsiveContainer>
    );
}

// ── Mount logic ────────────────────────────────────────────────

function mountChart(id, Component, props) {
    const el = document.getElementById(id);
    if (!el) return;

    // Dynamically import createRoot to avoid issues if not needed
    import('react-dom/client').then(({ createRoot }) => {
        const root = createRoot(el);
        root.render(
            <React.StrictMode>
                <Component {...props} />
            </React.StrictMode>
        );
    });
}

document.addEventListener('DOMContentLoaded', () => {

    // ── Manager Dashboard ──────────────────────────────────────
    if (window.HMS_DASHBOARD) {
        const d = window.HMS_DASHBOARD;
        mountChart('revenue-chart',          RevenueChart,        { data: d.dailyRevenue });
        mountChart('room-status-chart',      RoomStatusChart,     { data: d.roomStatus });
        mountChart('bookings-chart',         BookingsChart,       { data: d.dailyBookings });
        mountChart('monthly-revenue-chart',  MonthlyRevenueChart, { data: d.monthlyRevenue });
    }

    // ── Revenue Report ─────────────────────────────────────────
    if (window.HMS_REPORT_REVENUE) {
        mountChart('report-revenue-chart', ReportRevenueChart, { data: window.HMS_REPORT_REVENUE });
    }

    // ── Occupancy Report ───────────────────────────────────────
    if (window.HMS_REPORT_OCCUPANCY) {
        mountChart('occupancy-chart', OccupancyChart, { data: window.HMS_REPORT_OCCUPANCY });
    }
});