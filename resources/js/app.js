import './bootstrap';

// Import jsPDF for PDF export functionality
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

// Make jsPDF and autoTable available globally for dashboard
window.jsPDF = jsPDF;
window.jspdf = { jsPDF };
window.autoTable = autoTable;
