<template>
    <div class="page" v-for="person in pagesToRender" :key="person.id || person.navn || person" :style="pageStyle" :class="{ preview: !showData }">
        <div class="content">
            <p class="line">Navn</p>
            <p class="line name-line" :style="nameStyle(displayName(person))">{{ displayName(person) }}</p>
            <p class="line">Sted</p>
            <p class="line place-line" :style="placeStyle(displayPlaceLabel)">{{ displayPlaceLabel }}</p>
            <p class="line">År</p>
            <p class="line season-line">{{ displaySeasonLabel }}</p>
            <img class="logo" :class="logoClass" :src="computedLogoPath" :style="logoStyle" alt="UKM logo" />
        </div>
        <div class="footer" :class="{ preview: !showData, pdf: showData }">Diplom</div>
    </div>
</template>

<script lang="ts">
export default {
    data() {
        return {
            // Bruk binding for å unngå at bundleren prøver å importere filen
            logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogomorkbrun.svg',
            participants: (window as any).diplomPersoner || [],
            // Toggle populated content; defaults to preview-only view on screen
            showData: Boolean((window as any).diplomShowData),
            themeKey: (window as any).diplomTheme || 'purple',
            previewOnly: Boolean((window as any).diplomPreviewOnly),
            placeOverride: (window as any).diplomPlaceLabel || ''
            ,
            seasonOverride: (window as any).diplomSeasonLabel || ''
        };
    },
    mounted() {
        // Expose a controlled setter so PDF generation can toggle showing data
        (window as any).diplomSetShowData = (value: boolean) => {
            this.showData = Boolean(value);
        };
        (window as any).diplomSetTheme = (value: string) => {
            this.themeKey = value || 'purple';
        };
        (window as any).diplomSetPreviewOnly = (value: boolean) => {
            this.previewOnly = Boolean(value);
        };
        (window as any).diplomSetPlaceLabel = (value: string) => {
            this.placeOverride = value || '';
        };
        (window as any).diplomSetSeasonLabel = (value: string) => {
            this.seasonOverride = value || '';
        };
    },
    beforeUnmount() {
        if ((window as any).diplomSetShowData) {
            delete (window as any).diplomSetShowData;
        }
        if ((window as any).diplomSetTheme) {
            delete (window as any).diplomSetTheme;
        }
        if ((window as any).diplomSetPreviewOnly) {
            delete (window as any).diplomSetPreviewOnly;
        }
        if ((window as any).diplomSetPlaceLabel) {
            delete (window as any).diplomSetPlaceLabel;
        }
        if ((window as any).diplomSetSeasonLabel) {
            delete (window as any).diplomSetSeasonLabel;
        }
    },
    computed: {
        pages(): any[] {
            if (Array.isArray(this.participants) && this.participants.length > 0) {
                return this.participants;
            }
            // fallback single page
            return [''];
        },
        pagesToRender(): any[] {
            // Only show actual persons when explicitly requested (e.g. during PDF generation)
            if (!this.showData) {
                return [''];
            }

            return this.previewOnly ? this.pages.slice(0, 1) : this.pages;
        },
        logoStyle(): { marginTop: string; transform: string } {
            return {
                marginTop: this.showData ? '0' : '0',
                transform: this.showData ? 'translateY(-10mm)' : 'translateY(-16mm)'
            };
        },
        pageStyle(): { background: string; color: string } {
            return {
                background: this.theme.background,
                color: this.theme.textColor
            };
        },
        computedLogoPath(): string {
            return this.theme.logoPath || this.logoPath;
        },
        theme(): { background: string; logoPath: string; textColor: string } {
            const themes: { [key: string]: { background: string; logoPath: string; textColor: string } } = {
                dark: {
                    background: '#241211',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogolilla.svg',
                    textColor: '#f5eee4'
                },
                purple: {
                    background: '#ad83ff',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogomorkbrun.svg',
                    textColor: '#15082a'
                },
                orange: {
                    background: '#ff520e',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogomorkbrun.svg',
                    textColor: '#15082a'
                },
                dark_orange: {
                    background: '#241211',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogoorange.svg',
                    textColor: '#f5eee4'
                }
            };

            return themes[this.themeKey] || themes.purple;
        },
        logoClass(): { [key: string]: boolean } {
            return {
                'logo--pdf': this.showData,
                'logo--preview': !this.showData
            };
        },
        displayPlaceLabel(): string {
            if (this.placeOverride && this.placeOverride.trim().length > 0) {
                return this.stripYearFromPlace(this.placeOverride);
            }

            if (this.showData) {
                return this.placeLabel;
            }

            const placeType = (window as any).diplomPlaceType;
            if (placeType === 'fylke') {
                return 'Fylkesfestival Fylke';
            }

            return 'UKM Sted';
        },
        displaySeasonLabel(): string {
            if (this.seasonOverride && this.seasonOverride.trim().length > 0) {
                return this.seasonOverride.trim();
            }

            if (this.showData) {
                return this.seasonLabel;
            }

            return 'Sesong';
        },
        placeLabel(): string {
            if (this.placeOverride && this.placeOverride.trim().length > 0) {
                return this.stripYearFromPlace(this.placeOverride);
            }

            const director: any = (window as any).director;
            const getParam = (key: string) => {
                try {
                    return director && typeof director.getParam === 'function' ? director.getParam(key) : null;
                } catch (e) {
                    return null;
                }
            };

            const fylkeNavn = getParam('fylkeNavn') || getParam('fylke') || getParam('countyName');
            const kommuneNavn = getParam('kommuneNavn') || getParam('kommune') || getParam('municipalityName');

            // Kombinert etikett hvis begge finnes
            if (kommuneNavn && fylkeNavn) {
                return `UKM ${kommuneNavn} / Fylkesfestival ${fylkeNavn}`;
            }

            // Fylke-only
            if (fylkeNavn) {
                return `Fylkesfestival ${fylkeNavn}`;
            }

            // Kommune-only
            if (kommuneNavn) {
                return `UKM ${kommuneNavn}`;
            }

            return 'UKM';
        },
        seasonLabel(): string {
            if (this.seasonOverride && this.seasonOverride.trim().length > 0) {
                return this.seasonOverride.trim();
            }

            const director: any = (window as any).director;
            const getParam = (key: string) => {
                try {
                    return director && typeof director.getParam === 'function' ? director.getParam(key) : null;
                } catch (e) {
                    return null;
                }
            };

            return getParam('sesong') || getParam('season') || getParam('year') || '';
        }
    }
    ,
    methods: {
        stripYearFromPlace(text: string): string {
            const normalized = (text || '').trim();
            if (!normalized) {
                return '';
            }

            // Remove standalone 4-digit years (e.g. 2026) and tidy separators.
            const withoutYear = normalized.replace(/\b(19|20)\d{2}\b/g, '').replace(/\s{2,}/g, ' ');
            return withoutYear.replace(/\s*[-/|,]\s*$/g, '').trim();
        },
        getFontSizeFor(text: string, base: number): string {
            const normalized = (text || '').trim();
            const length = normalized.length;

            if (length > 38) {
                return `${Math.max(26, base - 16)}px`;
            }
            if (length > 30) {
                return `${Math.max(30, base - 12)}px`;
            }
            if (length > 22) {
                return `${Math.max(34, base - 8)}px`;
            }

            return `${base}px`;
        },
        nameStyle(text: string): { fontSize: string } {
            return { fontSize: this.getFontSizeFor(text, 44) };
        },
        placeStyle(text: string): { fontSize: string } {
            return { fontSize: this.getFontSizeFor(text, 44) };
        },
        displayName(person: any): string {
            if (this.showData) {
                return person?.navn || person?.name || person || '';
            }

            return 'Navn Navnesen';
        }
    }
};
</script>

<style scoped>
@font-face {
    font-family: 'TWKBurns Ultra';
    src: url('/wp-content/plugins/UKMrapporter/client/dist/assets/fonts/TWKBurns-Ultra.woff2') format('woff2'),
         url('/wp-content/plugins/UKMrapporter/client/dist/assets/fonts/TWKBurns-Ultra.woff') format('woff');
    font-weight: 800;
    font-style: normal;
    font-display: swap;
}

.page {
    width: 210mm;
    height: 297mm;
    background: transparent;
    margin: 0 auto;
    position: relative;
    padding: 15mm 0mm 15mm 0mm;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;
    page-break-after: always;
}

.page.preview {
    transform: scale(0.8);
    transform-origin: top left;
}

.page:last-child {
    page-break-after: auto;
}


.content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    color: inherit;
    font-family: 'TWKBurns Ultra', Arial, sans-serif;
    font-size: 36px;
    line-height: 1.1;
    gap: 10px;
    width: 100%;
    padding-left: 12mm;
}

.line {
    margin: 0;
}

.name-line {
    font-size: 44px;
    margin-top: -24px;
    word-break: break-word;
}

.place-line {
    font-size: 44px;
    margin-top: -24px;
    word-break: break-word;
}

.season-line {
    font-size: 44px;
    margin-top: -24px;
}

.logo {
    margin-top: 12px;
    max-width: none;
    height: auto;
    align-self: center;
}

.logo--preview {
    width: 130%;
}

.logo--pdf {
    width: 140%;
}

.footer {
    position: absolute;
    bottom: 1mm;
    left: -5mm;
    right: -5mm;
    font-family: 'TWKBurns Ultra', Arial, sans-serif;
    font-size: 200px;
    color: inherit;
    text-align: center;
}

.footer.pdf {
    transform: translateY(6mm);
}

.footer.preview {
    transform: translateY(3mm);
}
</style>