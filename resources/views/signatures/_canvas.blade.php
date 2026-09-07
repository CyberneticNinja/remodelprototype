<canvas id="signatureCanvas"
    class="border-2 border-gray-300 rounded w-full touch-none"
    style="height: 200px; cursor: crosshair;">
</canvas>

<div class="flex gap-2 mt-3 mb-6">
    <button type="button" id="clearBtn"
        class="px-4 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 text-gray-600">
        Clear
    </button>
</div>

<script>
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.strokeStyle = '#1a1a1a';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
    resizeCanvas();

    let drawing = false;

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches ? e.touches[0] : e;
        return {
            x: touch.clientX - rect.left,
            y: touch.clientY - rect.top
        };
    }

    canvas.addEventListener('mousedown',  e => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove',  e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup',    () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);

    canvas.addEventListener('touchstart',  e => { e.preventDefault(); drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); }, { passive: false });
    canvas.addEventListener('touchmove',   e => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, { passive: false });
    canvas.addEventListener('touchend',    () => drawing = false);

    document.getElementById('clearBtn').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    document.getElementById('signatureForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const data = canvas.toDataURL('image/png');
        const blank = document.createElement('canvas');
        blank.width = canvas.width;
        blank.height = canvas.height;
        if (data === blank.toDataURL('image/png')) {
            alert('Please sign before submitting.');
            return;
        }
        document.getElementById('signatureData').value = data;
        this.submit();
    });
</script>
