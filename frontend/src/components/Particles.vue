<template>
    <div>
        <div id="particles">
            <vue-particles id="tsparticles" :particlesInit="particlesInit" :options="{
                fpsLimit: 120,
                interactivity: {
                    detect_on: 'canvas',
                    events: {
                        onhover: {
                            enable: true,
                            mode: 'grab',
                        },
                        onclick: {
                            enable: true,
                            mode: 'push',
                        },
                        resize: true,
                    },
                    modes: {
                        grab: {
                            distance: 180,
                            line_linked: {
                                opacity: 0.9,
                            },
                        },
                        bubble: {
                            distance: 400,
                            size: 40,
                            duration: 2,
                            opacity: 8,
                            speed: 3,
                        },
                        repulse: {
                            distance: 50,
                            duration: 0,
                        },
                        push: {
                            particles_nb: 1,
                        },
                        remove: {
                            particles_nb: 2,
                        },
                    },
                },
                particles: {
                    number: {
                        value: 30,
                        density: {
                            enable: true,
                            value_area: 700,
                        },
                    },
                    color: {
                        value: '#016769',
                    },
                    shape: {
                        type: 'image',
                        stroke: {
                            width: 3,
                            color: '#8ee02f',
                        },
                        polygon: {
                            nb_sides: 5,
                        },
                        image: {
                            src: 'media/icons/logo.png',
                            width: 64,
                            height: 60
                        }
                    },
                    opacity: {
                        value: 1,
                        random: false,
                        anim: {
                            enable: false,
                            speed: 0.5,
                            opacity_min: 0.1,
                            sync: false,
                        },
                    },
                    size: {
                        value: 11,
                        random: true,
                        anim: {
                            enable: true,
                            speed: 3,
                            size_min: 0.3,
                            sync: false,
                        },
                    },
                    line_linked: {
                        enable: true,
                        distance: 160,
                        color: '#8ee02f',
                        opacity: 0.4,
                        width: 1,
                    },
                    move: {
                        enable: true,
                        speed: 1.7,
                        direction: 'none',
                        random: false,
                        straight: false,
                        out_mode: 'bounce',
                        bounce: false,
                        attract: {
                            enable: false,
                            rotateX: 1200,
                            rotateY: 300,
                        },
                    },
                },
                detectRetina: true
            }" />
        </div>
    </div>
</template>

<script setup>
import { loadSlim } from "tsparticles-slim"

const particlesInit = async engine => {
    await loadSlim(engine)
}
</script>

<style></style>