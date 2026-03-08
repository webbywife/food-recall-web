<?php
require_once __DIR__ . '/../includes/auth.php';
start_session();
$user = current_user();
if ($user) {
    header('Location: ' . ($user['role'] === 'supervisor' ? 'supervisor.php' : 'interviewer.php'));
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="FoodRecord — The Philippine 24-Hour Dietary Recall System. Built for FNRI-DOST NNS 2024. Aligned with Philippine RENI 2015.">
<title>FoodRecord — Philippine 24-Hour Dietary Recall System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ═══ RESET ═══════════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  font-family:'DM Sans','Trebuchet MS',system-ui,sans-serif;
  background:#FFF9F0;color:#1C1C1C;
  overflow-x:hidden;-webkit-font-smoothing:antialiased;
}
img,svg{display:block}
a{text-decoration:none}

/* ═══ TOKENS ══════════════════════════════════════════════════════ */
:root{
  --gd:   #0F1F35;          /* very dark navy */
  --g900: #1A3A5C;          /* navy */
  --g800: #C1272D;          /* Philippine red — primary brand */
  --g600: #D93025;          /* medium red */
  --g100: #FDEEE8;          /* light warm pink */
  --g50:  #FFF9F0;          /* warm cream surface */
  --gold: #F5A623;          /* warm gold */
  --gold-l:#FCEDC0;         /* light gold */
  --gold-d:#8B6914;
  --cream:#FFF9F0;          /* warm off-white */
  --c2:   #FEF3DC;          /* light gold tint */
  --wh:   #FFFFFF;
  --t:    #1C1C1C;
  --t2:   #1A3A5C;          /* navy for headings */
  --t3:   #6B6B6B;
  --bdr:  rgba(26,58,92,.13);
  --shd:  0 4px 24px rgba(26,58,92,.10);
  --shd2: 0 12px 48px rgba(26,58,92,.16);
  --fd:   'Playfair Display',Baskerville,Georgia,serif;
  --fb:   'DM Sans','Trebuchet MS',system-ui,sans-serif;
  --mw:   1160px;
  --nh:   66px;
  --rad:  10px;
  --rad2: 18px;
}

/* ═══ UTILITIES ═══════════════════════════════════════════════════ */
.wrap{max-width:var(--mw);margin:0 auto}
.fade{opacity:0;transform:translateY(22px);transition:opacity .7s ease,transform .7s ease}
.fade.vis{opacity:1;transform:none}
.fd1{transition-delay:.08s}.fd2{transition-delay:.17s}.fd3{transition-delay:.26s}
.fd4{transition-delay:.35s}.fd5{transition-delay:.44s}

/* ═══ NAV ══════════════════════════════════════════════════════════ */
#nav{
  position:fixed;top:0;left:0;right:0;z-index:900;
  height:var(--nh);display:flex;align-items:center;
  padding:0 2rem;transition:background .35s,box-shadow .35s;
}
#nav.ink{
  background:rgba(15,31,53,.96);
  box-shadow:0 2px 24px rgba(0,0,0,.28);
  backdrop-filter:blur(14px);
}
.nav-in{
  width:100%;max-width:var(--mw);margin:0 auto;
  display:flex;align-items:center;justify-content:space-between;
}
.logo{display:flex;align-items:center;gap:.6rem;color:#fff}
.logo-mark{
  width:34px;height:34px;background:var(--gold);border-radius:8px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.logo-name{
  font-family:var(--fd);font-size:1.35rem;font-weight:700;
  letter-spacing:-.03em;color:#fff;
}
.logo-name b{color:var(--gold-l);font-weight:700}
.nav-menu{display:flex;align-items:center;gap:1.75rem;list-style:none}
.nav-menu a{color:rgba(255,255,255,.78);font-size:.875rem;font-weight:500;transition:color .2s}
.nav-menu a:hover{color:var(--gold-l)}
.nav-signin{
  background:var(--gold);color:var(--gd) !important;
  padding:.45rem 1.2rem;border-radius:6px;
  font-weight:800;font-size:.82rem;letter-spacing:.04em;text-transform:uppercase;
  transition:background .2s,transform .15s !important;
}
.nav-signin:hover{background:var(--gold-l) !important;transform:translateY(-1px)}
.burger{
  display:none;flex-direction:column;gap:5px;cursor:pointer;
  background:none;border:none;padding:5px;
}
.burger span{display:block;width:22px;height:2px;background:#fff;border-radius:2px;transition:.3s}

/* ═══ HERO ══════════════════════════════════════════════════════════ */
.hero{
  position:relative;min-height:100vh;display:flex;
  align-items:center;justify-content:center;
  background:var(--gd);overflow:hidden;
}
.hero-diamonds{
  position:absolute;inset:0;
  background-image:url("data:image/svg+xml,%3Csvg width='90' height='90' viewBox='0 0 90 90' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='rgba(255,255,255,0.055)' stroke-width='0.7'%3E%3Cpath d='M45 0L90 45L45 90L0 45Z'/%3E%3Cpath d='M45 14L76 45L45 76L14 45Z'/%3E%3Cpath d='M45 28L62 45L45 62L28 45Z'/%3E%3C/g%3E%3C/svg%3E");
  background-size:90px 90px;
}
.hero-glow{
  position:absolute;inset:0;
  background:
    radial-gradient(ellipse 55% 65% at 25% 55%, rgba(193,39,45,.45) 0%,transparent 70%),
    radial-gradient(ellipse 35% 45% at 80% 25%, rgba(26,58,92,.55) 0%,transparent 65%),
    radial-gradient(ellipse 30% 40% at 70% 80%, rgba(245,166,35,.08) 0%,transparent 60%);
}

/* Particles */
.ptcls{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.p{position:absolute;opacity:0;animation:floatUp linear infinite}
@keyframes floatUp{
  0%  {transform:translateY(0)rotate(0deg)scale(1);opacity:0}
  6%  {opacity:.9}
  90% {opacity:.5}
  100%{transform:translateY(-115vh)rotate(540deg)scale(.4);opacity:0}
}
.pg{width:4px;height:11px;border-radius:50%;background:rgba(201,168,76,.55)}
.pd{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.18)}
.pl{width:7px;height:16px;border-radius:50% 50% 50% 0;background:rgba(76,175,80,.42);transform-origin:center}
.pr{width:9px;height:9px;border-radius:50%;border:1.5px solid rgba(201,168,76,.4);background:transparent}
.ps{width:5px;height:5px;background:rgba(255,255,255,.12);border-radius:1px;transform:rotate(45deg)}
.pa{ left: 3%;bottom:-10px;animation-duration:16s;animation-delay: 0s}
.pb{ left: 8%;bottom:-10px;animation-duration:20s;animation-delay: 3s}
.pc{ left:13%;bottom:-10px;animation-duration:13s;animation-delay: 7s}
.pd2{left:19%;bottom:-10px;animation-duration:18s;animation-delay: 1s}
.pe{ left:25%;bottom:-10px;animation-duration:14s;animation-delay: 5s}
.pf{ left:32%;bottom:-10px;animation-duration:22s;animation-delay: 2s}
.pg2{left:39%;bottom:-10px;animation-duration:15s;animation-delay: 9s}
.ph{ left:46%;bottom:-10px;animation-duration:19s;animation-delay: 0.5s}
.pi{ left:54%;bottom:-10px;animation-duration:12s;animation-delay: 6s}
.pj{ left:61%;bottom:-10px;animation-duration:17s;animation-delay: 4s}
.pk{ left:68%;bottom:-10px;animation-duration:21s;animation-delay: 8s}
.pl2{left:75%;bottom:-10px;animation-duration:14s;animation-delay: 1.5s}
.pm{ left:82%;bottom:-10px;animation-duration:16s;animation-delay:10s}
.pn{ left:89%;bottom:-10px;animation-duration:18s;animation-delay: 3.5s}
.po{ left:95%;bottom:-10px;animation-duration:11s;animation-delay: 6.5s}

.hero-body{
  position:relative;z-index:10;text-align:center;
  padding:calc(var(--nh) + 3.5rem) 1.5rem 5rem;
  max-width:820px;
}
.h-eyebrow{
  display:inline-flex;align-items:center;gap:.5rem;
  background:rgba(201,168,76,.12);border:1px solid rgba(201,168,76,.28);
  border-radius:100px;padding:.32rem 1rem;
  font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
  color:var(--gold-l);margin-bottom:1.4rem;
}
.h-dot{
  width:6px;height:6px;border-radius:50%;background:var(--gold);
  animation:pulse 2s ease-in-out infinite;
}
@keyframes pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.5);opacity:.6}}

.h-title{
  font-family:var(--fd);
  font-size:clamp(2.7rem,6.5vw,5.2rem);
  font-weight:700;line-height:1.04;letter-spacing:-.035em;
  color:#fff;margin-bottom:1.4rem;
}
.h-title em{font-style:italic;color:var(--gold-l)}
.h-sub{
  font-size:clamp(.95rem,2vw,1.15rem);line-height:1.7;
  color:rgba(255,255,255,.65);max-width:540px;margin:0 auto 2.5rem;
}
.h-ctas{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap}
.btn-gold{
  display:inline-flex;align-items:center;gap:.45rem;
  background:var(--gold);color:var(--gd);padding:.85rem 1.9rem;
  border-radius:7px;font-weight:800;font-size:.95rem;letter-spacing:.025em;
  transition:background .2s,transform .15s,box-shadow .2s;
  box-shadow:0 4px 20px rgba(201,168,76,.38);
}
.btn-gold:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 32px rgba(201,168,76,.52)}
.btn-ghost{
  display:inline-flex;align-items:center;gap:.45rem;
  background:transparent;color:rgba(255,255,255,.8);
  padding:.85rem 1.9rem;border-radius:7px;
  font-weight:600;font-size:.95rem;
  border:1.5px solid rgba(255,255,255,.22);
  transition:border-color .2s,color .2s,transform .15s;
}
.btn-ghost:hover{border-color:rgba(255,255,255,.55);color:#fff;transform:translateY(-2px)}
.hero-scroll{
  position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);
  display:flex;flex-direction:column;align-items:center;gap:.4rem;
  color:rgba(255,255,255,.3);font-size:.65rem;letter-spacing:.14em;text-transform:uppercase;
  animation:bob 2.2s ease-in-out infinite;
}
@keyframes bob{0%,100%{transform:translateX(-50%)translateY(0)}50%{transform:translateX(-50%)translateY(7px)}}

/* ═══ WHAT IS FOODRECORD ══════════════════════════════════════════ */
.what{background:var(--cream);padding:6rem 1.5rem;text-align:center}
.s-label{
  display:inline-block;font-size:.7rem;font-weight:800;letter-spacing:.16em;
  text-transform:uppercase;color:var(--g800);margin-bottom:.65rem;
}
.s-title{
  font-family:var(--fd);font-size:clamp(1.75rem,4vw,2.9rem);
  font-weight:700;letter-spacing:-.03em;color:var(--t);
  line-height:1.08;margin-bottom:1.1rem;
}
.s-sub{
  font-size:1rem;line-height:1.8;color:var(--t3);
  max-width:600px;margin:0 auto 3.5rem;
}
.stat-row{
  display:grid;grid-template-columns:repeat(3,1fr);
  gap:1.5rem;max-width:820px;margin:0 auto;
}
.scard{
  background:var(--wh);border:1px solid var(--bdr);border-radius:var(--rad2);
  padding:2rem 1.5rem;position:relative;overflow:hidden;
  transition:transform .25s,box-shadow .25s;
}
.scard::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,var(--g800),var(--gold));
}
.scard:hover{transform:translateY(-5px);box-shadow:var(--shd2)}
.scard-num{
  font-family:var(--fd);font-size:2.9rem;font-weight:700;
  color:var(--g900);line-height:1;margin-bottom:.3rem;
}
.scard-name{font-size:.83rem;font-weight:800;color:var(--t2);letter-spacing:.04em;margin-bottom:.3rem}
.scard-desc{font-size:.78rem;color:var(--t3);line-height:1.55}

/* ═══ FEATURES ══════════════════════════════════════════════════ */
.feats{
  background:var(--gd);padding:6rem 1.5rem;
  position:relative;overflow:hidden;
}
.feats::before{
  content:'';position:absolute;inset:0;
  background-image:url("data:image/svg+xml,%3Csvg width='70' height='70' viewBox='0 0 70 70' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='none' stroke='rgba(255,255,255,0.03)' stroke-width='0.6' d='M35 0L70 35L35 70L0 35Z'/%3E%3C/svg%3E");
  background-size:70px 70px;
}
.feats-in{position:relative;text-align:center}
.feats .s-label{color:var(--gold)}
.feats .s-title{color:#fff}
.feats .s-sub{color:rgba(255,255,255,.55);margin-bottom:3rem}
.fgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.1rem;margin-bottom:2.75rem}
.fc{
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);
  border-radius:var(--rad2);padding:1.9rem 1.4rem;text-align:left;
  transition:background .25s,border-color .25s,transform .25s;cursor:default;
}
.fc:hover{background:rgba(255,255,255,.08);border-color:rgba(201,168,76,.35);transform:translateY(-3px)}
.fc-icon{
  width:50px;height:50px;background:rgba(201,168,76,.11);border-radius:11px;
  display:flex;align-items:center;justify-content:center;
  margin-bottom:1.15rem;transition:background .25s;
}
.fc:hover .fc-icon{background:rgba(201,168,76,.2)}
.fc h3{
  font-family:var(--fd);font-size:1.1rem;font-weight:700;
  color:#fff;margin-bottom:.5rem;letter-spacing:-.02em;
}
.fc p{font-size:.84rem;line-height:1.65;color:rgba(255,255,255,.52)}
.btn-og{
  display:inline-flex;align-items:center;gap:.45rem;
  border:1.5px solid var(--gold);color:var(--gold);
  padding:.75rem 1.9rem;border-radius:7px;font-weight:700;font-size:.9rem;
  transition:background .2s,color .2s,transform .15s;
}
.btn-og:hover{background:var(--gold);color:var(--gd);transform:translateY(-2px)}

/* ═══ HOW IT WORKS ═════════════════════════════════════════════ */
.hiw{background:var(--c2);padding:6rem 1.5rem}
.hiw-in{text-align:center}
.hiw .s-sub{margin-bottom:3.5rem}
.steps{
  display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;
  position:relative;max-width:900px;margin:0 auto;
}
.steps::after{
  content:'';position:absolute;
  top:32px;left:calc(10% + 16px);right:calc(10% + 16px);
  height:1.5px;
  background:linear-gradient(90deg,var(--g100) 0%,var(--gold) 50%,var(--g100) 100%);
  z-index:0;
}
.step{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;text-align:center}
.step-orb{
  width:64px;height:64px;border-radius:50%;
  background:var(--wh);border:2px solid var(--g100);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:1rem;box-shadow:var(--shd);
  transition:border-color .25s,background .25s,transform .25s;
}
.step:hover .step-orb{border-color:var(--gold);background:var(--g50);transform:scale(1.1)}
.step-n{
  font-family:var(--fd);font-size:1.45rem;font-weight:700;
  color:var(--g900);line-height:1;
}
.step-t{font-size:.83rem;font-weight:800;color:var(--t);margin-bottom:.3rem;letter-spacing:.01em}
.step-d{font-size:.76rem;color:var(--t3);line-height:1.55}

/* ═══ FOR THE PHILIPPINES ═════════════════════════════════════ */
.ph{background:var(--wh);padding:6rem 1.5rem}
.ph-in{
  display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:center;
}
.ph-text .s-title{text-align:left;margin-bottom:1rem}
.ph-text p{font-size:.93rem;line-height:1.8;color:var(--t3);margin-bottom:1rem}
.ph-tags{display:flex;flex-wrap:wrap;gap:.5rem;margin:1.5rem 0 2rem}
.tag{
  display:inline-flex;align-items:center;gap:.35rem;
  background:var(--g50);color:var(--g800);border:1px solid var(--g100);
  border-radius:100px;padding:.3rem .8rem;font-size:.75rem;font-weight:800;letter-spacing:.04em;
}
.btn-green{
  display:inline-flex;align-items:center;gap:.45rem;
  background:var(--g800);color:#fff;
  padding:.8rem 1.7rem;border-radius:7px;font-weight:700;font-size:.88rem;
  transition:background .2s,transform .15s;
}
.btn-green:hover{background:#a01f22;transform:translateY(-2px)}

/* Illustration card */
.ph-card{
  background:linear-gradient(145deg,var(--g50),var(--c2));
  border:1px solid var(--bdr);border-radius:24px;padding:2.5rem;
  position:relative;overflow:hidden;
}
.ph-card::before{
  content:'';position:absolute;top:-40px;right:-40px;
  width:200px;height:200px;border-radius:50%;
  background:radial-gradient(circle,rgba(201,168,76,.1),transparent 70%);
}
.fg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.85rem}
.fgc{
  background:var(--wh);border:1px solid var(--bdr);border-radius:var(--rad);
  padding:1rem .75rem;text-align:center;
  transition:transform .2s,border-color .2s,box-shadow .2s;
}
.fgc:hover{transform:translateY(-2px);border-color:var(--gold);box-shadow:var(--shd)}
.fgc-ico{font-size:1.7rem;margin-bottom:.35rem;display:block;line-height:1}
.fgc-lbl{font-size:.7rem;font-weight:800;color:var(--t2);letter-spacing:.04em;line-height:1.3}
.ph-card-title{
  font-family:var(--fd);font-size:1rem;font-weight:700;color:var(--t2);
  margin-bottom:1.25rem;padding-bottom:.75rem;
  border-bottom:1px solid var(--bdr);letter-spacing:-.01em;
}
.ph-card-title span{color:var(--g800)}

/* ═══ RESEARCHERS ═════════════════════════════════════════════ */
.research{background:var(--c2);padding:6rem 1.5rem;text-align:center}
.rs-stats{
  display:flex;justify-content:center;align-items:center;
  gap:3.5rem;margin:2.75rem 0;flex-wrap:wrap;
}
.rs-item{text-align:center}
.rs-num{
  font-family:var(--fd);font-size:3.2rem;font-weight:700;
  color:var(--g900);line-height:1;display:block;margin-bottom:.3rem;
}
.rs-lbl{font-size:.77rem;font-weight:800;color:var(--t3);letter-spacing:.08em;text-transform:uppercase}
.rs-div{width:1px;height:56px;background:var(--bdr)}
.qcard{
  background:var(--wh);border:1px solid var(--bdr);border-radius:var(--rad2);
  padding:2.5rem 3rem;max-width:660px;margin:0 auto;
  position:relative;overflow:hidden;
}
.qcard::before{
  content:'\201C';position:absolute;top:-12px;left:2.5rem;
  font-family:var(--fd);font-size:6rem;color:var(--g100);line-height:1;
  pointer-events:none;
}
.q-text{
  font-family:var(--fd);font-size:1.1rem;font-style:italic;
  color:var(--t2);line-height:1.75;margin-bottom:1.25rem;position:relative;z-index:1;
}
.q-auth{font-size:.78rem;font-weight:800;color:var(--t3);letter-spacing:.06em;text-transform:uppercase}
.q-auth span{color:var(--g800)}

/* ═══ CTA ══════════════════════════════════════════════════════ */
.cta-strip{
  background:var(--g900);padding:5.5rem 1.5rem;text-align:center;
  position:relative;overflow:hidden;
}
.cta-strip::before{
  content:'';position:absolute;inset:0;
  background:
    radial-gradient(ellipse 80% 100% at 50% 50%,rgba(193,39,45,.3),transparent),
    url("data:image/svg+xml,%3Csvg width='70' height='70' viewBox='0 0 70 70' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='none' stroke='rgba(255,255,255,0.025)' stroke-width='0.5' d='M35 0L70 35L35 70L0 35Z'/%3E%3C/svg%3E");
  background-size:cover, 70px 70px;
}
.cta-in{position:relative;z-index:1}
.cta-strip .s-title{color:#fff;margin-bottom:.75rem}
.cta-strip p{color:rgba(255,255,255,.6);font-size:1rem;margin-bottom:2.25rem}
.btn-cta{
  display:inline-flex;align-items:center;gap:.5rem;
  background:var(--gold);color:var(--gd);
  padding:1rem 2.5rem;border-radius:8px;
  font-weight:800;font-size:1rem;letter-spacing:.03em;
  box-shadow:0 4px 24px rgba(201,168,76,.42);
  transition:background .2s,transform .15s,box-shadow .2s;
}
.btn-cta:hover{background:var(--gold-l);transform:translateY(-2px);box-shadow:0 8px 36px rgba(201,168,76,.58)}

/* ═══ FOOTER ═══════════════════════════════════════════════════ */
footer{background:var(--gd);padding:3rem 1.5rem}
.ft-in{
  max-width:var(--mw);margin:0 auto;
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:1.5rem;
}
.ft-logo{
  display:flex;align-items:center;gap:.5rem;
  font-family:var(--fd);font-size:1.1rem;color:rgba(255,255,255,.6);
}
.ft-links{display:flex;gap:1.5rem;list-style:none;flex-wrap:wrap}
.ft-links a{color:rgba(255,255,255,.35);font-size:.8rem;transition:color .2s}
.ft-links a:hover{color:var(--gold-l)}
.ft-copy{font-size:.75rem;color:rgba(255,255,255,.25)}

/* ═══ RESPONSIVE ═══════════════════════════════════════════════ */
@media(max-width:960px){
  .fgrid{grid-template-columns:repeat(2,1fr)}
  .steps{grid-template-columns:repeat(3,1fr)}
  .steps::after{display:none}
  .ph-in{grid-template-columns:1fr;gap:3rem}
}
@media(max-width:600px){
  .nav-menu{display:none}
  .nav-menu.open{
    display:flex;flex-direction:column;
    position:fixed;top:var(--nh);left:0;right:0;
    background:rgba(15,31,53,.97);padding:1.5rem 2rem;gap:1.25rem;
    backdrop-filter:blur(14px);
  }
  .burger{display:flex}
  .stat-row{grid-template-columns:1fr;max-width:280px}
  .fgrid{grid-template-columns:1fr}
  .steps{grid-template-columns:1fr}
  .rs-stats{gap:1.75rem}
  .rs-div{display:none}
  .qcard{padding:2rem 1.25rem}
  .ft-in{flex-direction:column;align-items:center;text-align:center}
}
</style>
</head>
<body>

<!-- ╔══════════════════════════════════════════════════╗
     ║  NAVIGATION                                      ║
     ╚══════════════════════════════════════════════════╝ -->
<header id="nav">
  <div class="nav-in">
    <a href="landing.php" class="logo">
      <div class="logo-mark">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path d="M10 2C10 2 5 6 5 11C5 13.76 7.24 16 10 16C12.76 16 15 13.76 15 11C15 6 10 2 10 2Z" fill="#0F1F35"/>
          <path d="M10 7V14M8 10H12" stroke="#0F1F35" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="logo-name">Food<b>Record</b></span>
    </a>
    <ul class="nav-menu" id="navMenu">
      <li><a href="#home">Home</a></li>
      <li><a href="#features">Features</a></li>
      <li><a href="#how-it-works">How It Works</a></li>
      <li><a href="#researchers">For Researchers</a></li>
      <li><a href="index.php" class="nav-signin">Sign In →</a></li>
    </ul>
    <button class="burger" id="burger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- ╔══════════════════════════════════════════════════╗
     ║  HERO                                            ║
     ╚══════════════════════════════════════════════════╝ -->
<section class="hero" id="home">
  <div class="hero-diamonds" aria-hidden="true"></div>
  <div class="hero-glow" aria-hidden="true"></div>

  <!-- Floating particles -->
  <div class="ptcls" aria-hidden="true">
    <span class="p pg pa"></span>
    <span class="p pd pb"></span>
    <span class="p pl pc"></span>
    <span class="p pr pd2"></span>
    <span class="p ps pe"></span>
    <span class="p pg pf"></span>
    <span class="p pd pg2"></span>
    <span class="p pl ph"></span>
    <span class="p pr pi"></span>
    <span class="p ps pj"></span>
    <span class="p pg pk"></span>
    <span class="p pd pl2"></span>
    <span class="p pl pm"></span>
    <span class="p pr pn"></span>
    <span class="p ps po"></span>
  </div>

  <div class="hero-body">
    <div class="h-eyebrow">
      <span class="h-dot"></span>
      FNRI-DOST · NNS 2024 · Philippine RENI 2015
    </div>
    <h1 class="h-title">
      The Philippine<br>
      <em>24-Hour Dietary</em><br>
      Recall System
    </h1>
    <p class="h-sub">Collect, analyze, and report dietary intake data for WRA and children 0–5 — aligned with FNRI-DOST protocols and Philippine RENI 2015 standards.</p>
    <div class="h-ctas">
      <a href="index.php" class="btn-gold">
        Get Started
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <a href="#about" class="btn-ghost">
        Learn More
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 9l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
  </div>

  <div class="hero-scroll" aria-hidden="true">
    Scroll
    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 3v12M4 10l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
  </div>
</section>

<!-- ╔══════════════════════════════════════════════════╗
     ║  WHAT IS FOODRECORD                              ║
     ╚══════════════════════════════════════════════════╝ -->
<section class="what" id="about">
  <div class="wrap">
    <p class="s-label fade">What is FoodRecord?</p>
    <h2 class="s-title fade fd1">Precision Nutrition Data<br>for the Philippines</h2>
    <p class="s-sub fade fd2">FoodRecord is a web-based CAPI dietary recall tool designed for the National Nutrition Survey. It guides interviewers through the validated 5-Pass AMPM method, automatically calculates nutrient intakes from the FNRI Food Composition Table, and benchmarks results against the 2015 RENI.</p>

    <div class="stat-row">
      <div class="scard fade fd1">
        <div class="scard-num">5-Pass</div>
        <div class="scard-name">AMPM Method</div>
        <div class="scard-desc">Quick List → Forgotten Foods → Time & Occasion → Detail Cycle → Review</div>
      </div>
      <div class="scard fade fd2">
        <div class="scard-num">135</div>
        <div class="scard-name">FNRI Food Items</div>
        <div class="scard-desc">Sourced from the FNRI Food Composition Tables 2013/2020, covering 15 Philippine food groups</div>
      </div>
      <div class="scard fade fd3">
        <div class="scard-num">RENI</div>
        <div class="scard-name">2015 Aligned</div>
        <div class="scard-desc">Automatic adequacy classification for 6 age/sex groups — energy, protein, fat, carbs, and fiber</div>
      </div>
    </div>
  </div>
</section>

<!-- ╔══════════════════════════════════════════════════╗
     ║  FEATURES                                        ║
     ╚══════════════════════════════════════════════════╝ -->
<section class="feats" id="features">
  <div class="wrap feats-in">
    <p class="s-label fade">Features</p>
    <h2 class="s-title fade fd1">Everything you need for<br>field nutrition surveys</h2>
    <p class="s-sub fade fd2" style="max-width:500px;margin-left:auto;margin-right:auto">Built for supervisors and interviewers in the Philippine nutrition research context.</p>

    <div class="fgrid">
      <div class="fc fade fd1">
        <div class="fc-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect x="5" y="3" width="14" height="18" rx="2" stroke="#C9A84C" stroke-width="1.6"/>
            <path d="M9 8h6M9 12h6M9 16h4" stroke="#C9A84C" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="7" cy="8" r="1" fill="#C9A84C"/>
            <circle cx="7" cy="12" r="1" fill="#C9A84C"/>
            <circle cx="7" cy="16" r="1" fill="#C9A84C"/>
          </svg>
        </div>
        <h3>Quick List Recall</h3>
        <p>Interviewers record all foods consumed in the past 24 hours using a fast, uninterrupted free-recall flow.</p>
      </div>
      <div class="fc fade fd2">
        <div class="fc-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="4" stroke="#C9A84C" stroke-width="1.6"/>
            <circle cx="5" cy="7" r="2" stroke="#C9A84C" stroke-width="1.4"/>
            <circle cx="19" cy="7" r="2" stroke="#C9A84C" stroke-width="1.4"/>
            <circle cx="5" cy="17" r="2" stroke="#C9A84C" stroke-width="1.4"/>
            <circle cx="19" cy="17" r="2" stroke="#C9A84C" stroke-width="1.4"/>
            <path d="M7 8.5L10 10.5M14 10.5L17 8.5M7 15.5L10 13.5M14 13.5L17 15.5" stroke="#C9A84C" stroke-width="1.2"/>
          </svg>
        </div>
        <h3>Automated Nutrient Analysis</h3>
        <p>Instantly calculates energy, protein, fat, carbohydrates, and fiber from the FNRI FCT. Adequacy scored against RENI 2015.</p>
      </div>
      <div class="fc fade fd3">
        <div class="fc-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect x="3" y="3" width="8" height="8" rx="1.5" stroke="#C9A84C" stroke-width="1.6"/>
            <rect x="13" y="3" width="8" height="8" rx="1.5" stroke="#C9A84C" stroke-width="1.6"/>
            <rect x="3" y="13" width="8" height="8" rx="1.5" stroke="#C9A84C" stroke-width="1.6"/>
            <path d="M13 17h8M17 13v8" stroke="#C9A84C" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <h3>Supervisor Dashboard</h3>
        <p>Real-time field coverage, interviewer quota tracking, household completion rates, and Day 2 recall scheduling in one view.</p>
      </div>
      <div class="fc fade fd4">
        <div class="fc-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect x="4" y="5" width="16" height="15" rx="2" stroke="#C9A84C" stroke-width="1.6"/>
            <path d="M4 9h16" stroke="#C9A84C" stroke-width="1.5"/>
            <path d="M8 3v4M16 3v4" stroke="#C9A84C" stroke-width="1.6" stroke-linecap="round"/>
            <path d="M9 13.5l2 2 4-4" stroke="#C9A84C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="18" cy="17" r="4" fill="#0F1F35" stroke="#C9A84C" stroke-width="1.5"/>
            <path d="M17 17h2M18 16v2" stroke="#C9A84C" stroke-width="1.2" stroke-linecap="round"/>
          </svg>
        </div>
        <h3>Day 2 Recall Sampling</h3>
        <p>Automatic 20% probability sampling for second-day recalls with configurable scheduling (3–10 day offset).</p>
      </div>
    </div>

    <div class="feat-cta fade">
      <a href="index.php" class="btn-og">
        Start Your Survey
        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M2 7.5h11M8.5 3.5l4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ╔══════════════════════════════════════════════════╗
     ║  HOW IT WORKS                                    ║
     ╚══════════════════════════════════════════════════╝ -->
<section class="hiw" id="how-it-works">
  <div class="wrap hiw-in">
    <p class="s-label fade">How It Works</p>
    <h2 class="s-title fade fd1">The 5-Pass Method</h2>
    <p class="s-sub fade fd2" style="margin-bottom:1rem">A validated multi-pass approach that maximizes completeness and accuracy of dietary recall data.</p>

    <div class="steps">
      <div class="step fade fd1">
        <div class="step-orb">
          <span class="step-n">1</span>
        </div>
        <div class="step-t">Quick List</div>
        <div class="step-d">Uninterrupted free recall of all foods consumed in the past 24 hours</div>
      </div>
      <div class="step fade fd2">
        <div class="step-orb">
          <span class="step-n">2</span>
        </div>
        <div class="step-t">Forgotten Foods</div>
        <div class="step-d">Filipino-specific probes for beverages, snacks, condiments & breastmilk</div>
      </div>
      <div class="step fade fd3">
        <div class="step-orb">
          <span class="step-n">3</span>
        </div>
        <div class="step-t">Time & Occasion</div>
        <div class="step-d">Assign meal occasion, time eaten, and place of consumption per food item</div>
      </div>
      <div class="step fade fd4">
        <div class="step-orb">
          <span class="step-n">4</span>
        </div>
        <div class="step-t">Detail Cycle</div>
        <div class="step-d">FCT food matching, portion size, cooking method, brand, and added ingredients</div>
      </div>
      <div class="step fade fd5">
        <div class="step-orb">
          <span class="step-n">5</span>
        </div>
        <div class="step-t">Review</div>
        <div class="step-d">Respondent confirmation, final additions, and RENI adequacy summary</div>
      </div>
    </div>
  </div>
</section>

<!-- ╔══════════════════════════════════════════════════╗
     ║  FOR THE PHILIPPINES                             ║
     ╚══════════════════════════════════════════════════╝ -->
<section class="ph">
  <div class="wrap ph-in">
    <div class="ph-text">
      <p class="s-label fade">Built for the Philippines</p>
      <h2 class="s-title fade fd1">Designed for NNS 2024 &amp; FNRI-DOST</h2>
      <p class="fade fd2">FoodRecord is purpose-built for the National Nutrition Survey protocols of the Philippines. Every detail — from food codes to portion size descriptors — reflects the Philippine food environment.</p>
      <p class="fade fd2">Targeted at two vulnerable groups central to Philippine nutrition policy:</p>
      <div class="ph-tags fade fd3">
        <span class="tag">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="4" r="2.5" stroke="currentColor" stroke-width="1.2"/><path d="M1.5 10.5c0-2.5 2-4 4.5-4s4.5 1.5 4.5 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
          Women of Reproductive Age (WRA)
        </span>
        <span class="tag">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="3.5" r="2" stroke="currentColor" stroke-width="1.2"/><path d="M2 10c0-2 1.8-3.5 4-3.5s4 1.5 4 3.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
          Children 0–5 Years
        </span>
        <span class="tag">FNRI Food Composition Table</span>
        <span class="tag">Philippine RENI 2015</span>
        <span class="tag">AMPM 5-Pass Protocol</span>
      </div>
      <a href="index.php" class="btn-green fade fd4">
        Access the System
        <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M2 7.5h11M8.5 3.5l4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>

    <div class="ph-card fade fd2">
      <div class="ph-card-title"><span>FNRI</span> Food Group Coverage</div>
      <div class="fg-grid">
        <div class="fgc">
          <span class="fgc-ico">🌾</span>
          <div class="fgc-lbl">Rice &amp; Grains</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🥬</span>
          <div class="fgc-lbl">Vegetables</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🐟</span>
          <div class="fgc-lbl">Fish &amp; Seafood</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🍌</span>
          <div class="fgc-lbl">Fruits</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🥛</span>
          <div class="fgc-lbl">Milk &amp; Dairy</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🥩</span>
          <div class="fgc-lbl">Meat &amp; Poultry</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🫘</span>
          <div class="fgc-lbl">Legumes</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🥚</span>
          <div class="fgc-lbl">Eggs</div>
        </div>
        <div class="fgc">
          <span class="fgc-ico">🫙</span>
          <div class="fgc-lbl">Condiments</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ╔══════════════════════════════════════════════════╗
     ║  FOR RESEARCHERS                                 ║
     ╚══════════════════════════════════════════════════╝ -->
<section class="research" id="researchers">
  <div class="wrap research-inner">
    <p class="s-label fade">For Researchers</p>
    <h2 class="s-title fade fd1">Validated. Rigorous. Philippine-specific.</h2>
    <p class="s-sub fade fd2">FoodRecord brings the same methodological rigor as international dietary recall systems, localized for the Philippine nutrition context.</p>

    <div class="rs-stats">
      <div class="rs-item fade fd1">
        <span class="rs-num" data-count="135">0</span>
        <span class="rs-lbl">FNRI Food Items</span>
      </div>
      <div class="rs-div fade"></div>
      <div class="rs-item fade fd2">
        <span class="rs-num" data-count="15">0</span>
        <span class="rs-lbl">Food Groups</span>
      </div>
      <div class="rs-div fade"></div>
      <div class="rs-item fade fd3">
        <span class="rs-num" data-count="6">0</span>
        <span class="rs-lbl">RENI Age/Sex Groups</span>
      </div>
      <div class="rs-div fade"></div>
      <div class="rs-item fade fd4">
        <span class="rs-num">5-Pass</span>
        <span class="rs-lbl">AMPM Protocol</span>
      </div>
    </div>

    <div class="qcard fade fd2">
      <p class="q-text">"Standardized dietary recall data collected with FoodRecord enables evidence-based nutrition policy decisions at national scale — with the cultural specificity the Philippines requires."</p>
      <p class="q-auth">Food and Nutrition Research Institute &nbsp;·&nbsp; <span>DOST, Philippines</span></p>
    </div>
  </div>
</section>

<!-- ╔══════════════════════════════════════════════════╗
     ║  CTA BANNER                                      ║
     ╚══════════════════════════════════════════════════╝ -->
<section class="cta-strip">
  <div class="wrap cta-in">
    <h2 class="s-title fade">Ready to start your nutrition survey?</h2>
    <p class="fade fd1">Sign in to access your dashboard and begin collecting dietary recall data.</p>
    <a href="index.php" class="btn-cta fade fd2">
      Sign In to FoodRecord
      <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M3 8.5h11M9.5 4.5l4 4-4 4" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
  </div>
</section>

<!-- ╔══════════════════════════════════════════════════╗
     ║  FOOTER                                          ║
     ╚══════════════════════════════════════════════════╝ -->
<footer>
  <div class="ft-in">
    <div class="ft-logo">
      <div class="logo-mark" style="width:28px;height:28px;border-radius:6px;background:rgba(201,168,76,.7);display:flex;align-items:center;justify-content:center">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M10 2C10 2 5 6 5 11C5 13.76 7.24 16 10 16C12.76 16 15 13.76 15 11C15 6 10 2 10 2Z" fill="#0F1F35"/><path d="M10 7V14M8 10H12" stroke="#0F1F35" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      FoodRecord
    </div>
    <ul class="ft-links">
      <li><a href="landing.php">Home</a></li>
      <li><a href="#features">Features</a></li>
      <li><a href="#how-it-works">How It Works</a></li>
      <li><a href="index.php">Sign In</a></li>
      <li><a href="help.php">Documentation</a></li>
    </ul>
    <span class="ft-copy">© 2026 FNRI-DOST Philippines. All rights reserved.</span>
  </div>
</footer>

<script>
// ── Nav scroll ──────────────────────────────────────────────────
const nav  = document.getElementById('nav');
const updateNav = () => nav.classList.toggle('ink', window.scrollY > 40);
window.addEventListener('scroll', updateNav, {passive:true});
updateNav();

// ── Mobile menu ─────────────────────────────────────────────────
const burger  = document.getElementById('burger');
const navMenu = document.getElementById('navMenu');
burger.addEventListener('click', () => {
  const open = navMenu.classList.toggle('open');
  burger.setAttribute('aria-expanded', open);
  const spans = burger.querySelectorAll('span');
  if (open) {
    spans[0].style.cssText = 'transform:rotate(45deg) translate(5px,5px)';
    spans[1].style.opacity = '0';
    spans[2].style.cssText = 'transform:rotate(-45deg) translate(5px,-5px)';
  } else {
    spans.forEach(s => s.style.cssText = '');
  }
});
navMenu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
  navMenu.classList.remove('open');
  burger.querySelectorAll('span').forEach(s => s.style.cssText = '');
}));

// ── Smooth section scroll ───────────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      const offset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nh')) || 66;
      window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
    }
  });
});

// ── Fade-in on scroll ───────────────────────────────────────────
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('vis'); io.unobserve(e.target); } });
}, { threshold: 0.12 });
document.querySelectorAll('.fade').forEach(el => io.observe(el));

// ── Counter animation ───────────────────────────────────────────
function animateCount(el) {
  const target = parseInt(el.dataset.count);
  if (isNaN(target)) return;
  const dur = 1400;
  const start = performance.now();
  const run = (now) => {
    const p = Math.min((now - start) / dur, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.round(ease * target);
    if (p < 1) requestAnimationFrame(run);
    else el.textContent = target;
  };
  requestAnimationFrame(run);
}
const cio = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.querySelectorAll('[data-count]').forEach(animateCount);
      cio.unobserve(e.target);
    }
  });
}, { threshold: 0.3 });
document.querySelectorAll('.rs-stats').forEach(el => cio.observe(el));
</script>
</body>
</html>
