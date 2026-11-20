/**
 * XDS Zimbabwe — Subtle Interactive Three.js 3D Data Intelligence Network
 * Graceful WebGL fallback, ResizeObserver, visibility pause & clean disposal.
 */
(function () {
  'use strict';

  function initHeroThree() {
    const canvas = document.getElementById('xds-hero-canvas');
    if (!canvas || !window.THREE) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    const container = canvas.parentElement;
    if (!container) return;

    let renderer;
    try {
      renderer = new window.THREE.WebGLRenderer({
        canvas: canvas,
        antialias: true,
        alpha: true,
        powerPreference: 'high-performance',
      });
    } catch (err) {
      // Graceful fallback: hero photographic + solid overlay remains intact
      return;
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

    const scene = new window.THREE.Scene();
    const camera = new window.THREE.PerspectiveCamera(54, 1, 0.1, 1000);
    camera.position.z = 115;

    const group = new window.THREE.Group();
    scene.add(group);

    // Generate 3D network nodes echoing XDS's signature data constellation
    const nodeCount = window.innerWidth < 768 ? 44 : 76;
    const spreadX = 165;
    const spreadY = 78;
    const spreadZ = 65;

    const positions = new Float32Array(nodeCount * 3);
    const colors = new Float32Array(nodeCount * 3);
    const velocities = [];

    const colorLime = new window.THREE.Color('#9AC93B');
    const colorIndigo = new window.THREE.Color('#6D7BFF');
    const colorWhite = new window.THREE.Color('#DDE6FF');

    for (let i = 0; i < nodeCount; i++) {
      const x = (Math.random() - 0.5) * spreadX;
      const y = (Math.random() - 0.5) * spreadY;
      const z = (Math.random() - 0.5) * spreadZ;

      positions[i * 3] = x;
      positions[i * 3 + 1] = y;
      positions[i * 3 + 2] = z;

      velocities.push({
        x: (Math.random() - 0.5) * 0.08,
        y: (Math.random() - 0.5) * 0.06,
        z: (Math.random() - 0.5) * 0.05,
      });

      const pick = Math.random();
      const c = pick < 0.38 ? colorLime : pick < 0.72 ? colorIndigo : colorWhite;
      colors[i * 3] = c.r;
      colors[i * 3 + 1] = c.g;
      colors[i * 3 + 2] = c.b;
    }

    const pointGeo = new window.THREE.BufferGeometry();
    pointGeo.setAttribute('position', new window.THREE.BufferAttribute(positions, 3));
    pointGeo.setAttribute('color', new window.THREE.BufferAttribute(colors, 3));

    const pointMat = new window.THREE.PointsMaterial({
      size: 2.6,
      vertexColors: true,
      transparent: true,
      opacity: 0.9,
      blending: window.THREE.AdditiveBlending,
      depthWrite: false,
    });

    const pointsMesh = new window.THREE.Points(pointGeo, pointMat);
    group.add(pointsMesh);

    // Subtle geometric wireframe icosahedron accent on right side
    const icoGeo = new window.THREE.IcosahedronGeometry(26, 1);
    const icoMat = new window.THREE.MeshBasicMaterial({
      color: 0x9ac93b,
      wireframe: true,
      transparent: true,
      opacity: 0.14,
    });
    const icoMesh = new window.THREE.Mesh(icoGeo, icoMat);
    icoMesh.position.set(42, 4, -18);
    group.add(icoMesh);

    // Dynamic connecting lines between close nodes
    const maxSegments = nodeCount * 6;
    const linePositions = new Float32Array(maxSegments * 6);
    const lineGeo = new window.THREE.BufferGeometry();
    lineGeo.setAttribute('position', new window.THREE.BufferAttribute(linePositions, 3));

    const lineMat = new window.THREE.LineBasicMaterial({
      color: 0x9ac93b,
      transparent: true,
      opacity: 0.24,
      blending: window.THREE.AdditiveBlending,
      depthWrite: false,
    });

    const lineSegments = new window.THREE.LineSegments(lineGeo, lineMat);
    group.add(lineSegments);

    // Pointer parallax
    const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
    const onPointerMove = (e) => {
      const rect = container.getBoundingClientRect();
      pointer.tx = ((e.clientX - rect.left) / Math.max(rect.width, 1) - 0.5) * 2;
      pointer.ty = ((e.clientY - rect.top) / Math.max(rect.height, 1) - 0.5) * 2;
    };
    window.addEventListener('pointermove', onPointerMove, { passive: true });

    // Resize handling
    function handleResize() {
      const w = container.clientWidth || window.innerWidth;
      const h = container.clientHeight || 540;
      camera.aspect = w / Math.max(h, 1);
      camera.updateProjectionMatrix();
      renderer.setSize(w, h, false);
    }
    handleResize();

    let ro = null;
    if (typeof window.ResizeObserver === 'function') {
      ro = new window.ResizeObserver(handleResize);
      ro.observe(container);
    } else {
      window.addEventListener('resize', handleResize);
    }

    // Pause when tab hidden
    let isRunning = true;
    let rafId = null;

    const onVisibility = () => {
      isRunning = !document.hidden;
      if (isRunning && rafId === null) {
        rafId = requestAnimationFrame(animate);
      }
    };
    document.addEventListener('visibilitychange', onVisibility);

    const thresholdSq = 30 * 30;

    function animate() {
      if (!isRunning) {
        rafId = null;
        return;
      }

      const posAttr = pointGeo.attributes.position;
      const arr = posAttr.array;

      for (let i = 0; i < nodeCount; i++) {
        const ix = i * 3;
        arr[ix] += velocities[i].x;
        arr[ix + 1] += velocities[i].y;
        arr[ix + 2] += velocities[i].z;

        if (arr[ix] > spreadX / 2 || arr[ix] < -spreadX / 2) velocities[i].x *= -1;
        if (arr[ix + 1] > spreadY / 2 || arr[ix + 1] < -spreadY / 2) velocities[i].y *= -1;
        if (arr[ix + 2] > spreadZ / 2 || arr[ix + 2] < -spreadZ / 2) velocities[i].z *= -1;
      }
      posAttr.needsUpdate = true;

      let segIndex = 0;
      const lineArr = lineGeo.attributes.position.array;
      for (let i = 0; i < nodeCount && segIndex < maxSegments; i++) {
        const ax = arr[i * 3];
        const ay = arr[i * 3 + 1];
        const az = arr[i * 3 + 2];
        for (let j = i + 1; j < nodeCount && segIndex < maxSegments; j++) {
          const dx = ax - arr[j * 3];
          const dy = ay - arr[j * 3 + 1];
          const dz = az - arr[j * 3 + 2];
          if (dx * dx + dy * dy + dz * dz < thresholdSq) {
            const base = segIndex * 6;
            lineArr[base] = ax;
            lineArr[base + 1] = ay;
            lineArr[base + 2] = az;
            lineArr[base + 3] = arr[j * 3];
            lineArr[base + 4] = arr[j * 3 + 1];
            lineArr[base + 5] = arr[j * 3 + 2];
            segIndex++;
          }
        }
      }
      lineGeo.setDrawRange(0, segIndex * 2);
      lineGeo.attributes.position.needsUpdate = true;

      pointer.x += (pointer.tx - pointer.x) * 0.04;
      pointer.y += (pointer.ty - pointer.y) * 0.04;

      group.rotation.y = pointer.x * 0.12 + Math.sin(Date.now() * 0.00018) * 0.06;
      group.rotation.x = -pointer.y * 0.08;
      icoMesh.rotation.x += 0.0018;
      icoMesh.rotation.y += 0.0024;

      renderer.render(scene, camera);
      rafId = requestAnimationFrame(animate);
    }

    rafId = requestAnimationFrame(animate);

    // Clean disposal on page unload
    window.addEventListener('beforeunload', () => {
      isRunning = false;
      if (rafId !== null) cancelAnimationFrame(rafId);
      if (ro) ro.disconnect();
      window.removeEventListener('pointermove', onPointerMove);
      window.removeEventListener('resize', handleResize);
      document.removeEventListener('visibilitychange', onVisibility);
      pointGeo.dispose();
      pointMat.dispose();
      lineGeo.dispose();
      lineMat.dispose();
      icoGeo.dispose();
      icoMat.dispose();
      renderer.dispose();
    });
  }

  document.addEventListener('DOMContentLoaded', initHeroThree);
})();
