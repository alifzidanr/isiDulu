// Lucide is the icon set shadcn/ui uses. Only the icons the app needs are
// imported, to keep the bundle small. Use them in Blade with
// <x-ui.icon name="plus" /> (or <i data-lucide="plus"></i>).
import {
    createIcons,
    Activity, ArrowLeft, BadgeCheck, Ban, Building2, Calendar, Check, ChevronDown,
    ChevronLeft, ChevronRight, ChevronsUpDown, CircleCheck, CircleX, ClipboardList, Clock, Ellipsis,
    ExternalLink, Eye, EyeOff, FilePlus, Fingerprint, KeyRound, ShieldCheck, UserCog, FileText, GitBranch, Headset, Inbox, Info, Laptop, LayoutDashboard,
    ListChecks, LoaderCircle, LogIn, LogOut, Menu, Network, PanelLeft, Pencil, Play,
    Plus, Printer, Save, Send, StickyNote, Trash2, TriangleAlert, User, Users, Wrench, X,
} from 'lucide';

const icons = {
    Activity, ArrowLeft, BadgeCheck, Ban, Building2, Calendar, Check, ChevronDown,
    ChevronLeft, ChevronRight, ChevronsUpDown, CircleCheck, CircleX, ClipboardList, Clock, Ellipsis,
    ExternalLink, Eye, EyeOff, FilePlus, Fingerprint, KeyRound, ShieldCheck, UserCog, FileText, GitBranch, Headset, Inbox, Info, Laptop, LayoutDashboard,
    ListChecks, LoaderCircle, LogIn, LogOut, Menu, Network, PanelLeft, Pencil, Play,
    Plus, Printer, Save, Send, StickyNote, Trash2, TriangleAlert, User, Users, Wrench, X,
};

let scheduled = false;

export function renderIcons() {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(() => {
        scheduled = false;
        if (document.querySelector('i[data-lucide]')) {
            createIcons({ icons, attrs: { 'stroke-width': 2, 'aria-hidden': 'true' } });
        }
    });
}
