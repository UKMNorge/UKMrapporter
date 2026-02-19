<template>
    <div class="page" v-for="page in pagesToRender" :key="page.key" :style="pageStyle" :class="{ preview: previewOnly }">
        <div class="idkort-grid">
            <div class="idkort-card" v-for="person in page.items" :key="person.id || person.navn || person" :style="cardStyle">
                <img class="logo" :src="computedLogoPath" alt="UKM logo" />
                    <div class="idkort-hole"></div>
                <div class="name" :style="nameStyle(displayName(person))">{{ displayName(person) }}</div>
                <div class="role" :style="roleStyle(displayRole(person))">{{ displayRole(person) }}</div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
export default {
    data() {
        return {
            // Bruk binding for å unngå at bundleren prøver å importere filen
            logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogomorkbrun.svg',
            participants: (window as any).idkortPersoner || [],
            previewRole: (window as any).idkortPreviewRole || 'Rolle',
            showData: (window as any).idkortShowData !== undefined ? Boolean((window as any).idkortShowData) : true,
            themeKey: (window as any).idkortTheme || 'purple',
            previewOnly: Boolean((window as any).idkortPreviewOnly),
            participantsUpdateHandler: null as null | (() => void)
        };
    },
    mounted() {
        (window as any).idkortSetShowData = (value: boolean) => {
            this.showData = Boolean(value);
        };
        (window as any).idkortSetParticipants = (value: any[]) => {
            this.participants = Array.isArray(value) ? [...value] : [];
        };
        (window as any).idkortSetPreviewRole = (value: string) => {
            this.previewRole = (value || '').trim() || 'Rolle';
        };
        (window as any).idkortSetTheme = (value: string) => {
            this.themeKey = value || 'purple';
        };
        (window as any).idkortSetPreviewOnly = (value: boolean) => {
            this.previewOnly = Boolean(value);
        };

        this.participantsUpdateHandler = () => {
            const source = (window as any).idkortPersoner;
            this.participants = Array.isArray(source) ? [...source] : [];
        };
        window.addEventListener('idkortParticipantsUpdated', this.participantsUpdateHandler as EventListener);
    },
    beforeUnmount() {
        if ((window as any).idkortSetShowData) {
            delete (window as any).idkortSetShowData;
        }
        if ((window as any).idkortSetParticipants) {
            delete (window as any).idkortSetParticipants;
        }
        if ((window as any).idkortSetPreviewRole) {
            delete (window as any).idkortSetPreviewRole;
        }
        if ((window as any).idkortSetTheme) {
            delete (window as any).idkortSetTheme;
        }
        if ((window as any).idkortSetPreviewOnly) {
            delete (window as any).idkortSetPreviewOnly;
        }
        if (this.participantsUpdateHandler) {
            window.removeEventListener('idkortParticipantsUpdated', this.participantsUpdateHandler as EventListener);
            this.participantsUpdateHandler = null;
        }
    },
    computed: {
        pagesToRender(): any[] {
            const itemsPerPage = 9;
            const placeholders = Array.from({ length: itemsPerPage }, () => ({ navn: 'Navn Navnesen', rolle: this.previewRole || 'Rolle' }));

            if (!this.showData) {
                return [{ key: 'preview', items: placeholders }];
            }

            const participants = Array.isArray(this.participants) ? this.participants : [];
            if (participants.length === 0) {
                return [{ key: 'preview-empty', items: placeholders }];
            }
            const pages = [];

            for (let i = 0; i < participants.length; i += itemsPerPage) {
                pages.push({ key: `page-${i}`, items: participants.slice(i, i + itemsPerPage) });
            }

            if (pages.length === 0) {
                pages.push({ key: 'empty', items: [] });
            }

            return this.previewOnly ? pages.slice(0, 1) : pages;
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
        theme(): { background: string; logoPath: string; textColor: string; borderColor: string } {
            const themes: { [key: string]: { background: string; logoPath: string; textColor: string; borderColor: string } } = {
                dark: {
                    background: '#241211',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogolilla.svg',
                    textColor: '#f5eee4',
                    borderColor: 'rgba(0, 0, 0, 0.35)'
                },
                purple: {
                    background: '#ad83ff',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogomorkbrun.svg',
                    textColor: '#15082a',
                    borderColor: 'rgba(0, 0, 0, 0.35)'
                },
                orange: {
                    background: '#ff520e',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogomorkbrun.svg',
                    textColor: '#15082a',
                    borderColor: 'rgba(0, 0, 0, 0.35)'
                },
                brown: {
                    background: '#a56800',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogomorkbrun.svg',
                    textColor: '#15082a',
                    borderColor: 'rgba(0, 0, 0, 0.35)'
                },
                dark_brown_orange: {
                    background: '#241211',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogoorange.svg',
                    textColor: '#ff520e',
                    borderColor: '#f5eee4'
                },
                dark_orange: {
                    background: '#241211',
                    logoPath: '/wp-content/plugins/UKMrapporter/client/dist/assets/logos/ukmlogoorange.svg',
                    textColor: '#f5eee4',
                    borderColor: 'rgba(0, 0, 0, 0.35)'
                }
            };

            return themes[this.themeKey] || themes.purple;
        },
        cardStyle(): { borderColor: string } {
            return {
                borderColor: this.theme.borderColor || 'rgba(0, 0, 0, 0.35)'
            };
        }
    },
    methods: {
        givenNameFromPerson(person: any): string {
            const fornavn = (person?.fornavn || '').trim();
            if (fornavn) {
                return fornavn;
            }

            const rawName = (person?.navn || person?.name || person || '').trim();
            if (!rawName) {
                return '';
            }

            const parts = rawName.split(/\s+/).filter(Boolean);
            if (parts.length <= 1) {
                return rawName;
            }

            return parts.slice(0, -1).join(' ');
        },
        getFontSizeFor(text: string, base: number): string {
            const normalized = (text || '').trim();
            const length = normalized.length;

            if (length > 38) {
                return `${Math.max(20, base - 14)}px`;
            }
            if (length > 30) {
                return `${Math.max(24, base - 10)}px`;
            }
            if (length > 22) {
                return `${Math.max(28, base - 6)}px`;
            }

            return `${base}px`;
        },
        nameStyle(text: string): { fontSize: string } {
            const length = (text || '').trim().length;

            if (length > 30) {
                return { fontSize: '12px' };
            }
            if (length > 26) {
                return { fontSize: '13px' };
            }
            if (length > 22) {
                return { fontSize: '14px' };
            }
            if (length > 19) {
                return { fontSize: '15px' };
            }
            if (length > 16) {
                return { fontSize: '16px' };
            }
            if (length > 13) {
                return { fontSize: '17px' };
            }
            if (length > 10) {
                return { fontSize: '18px' };
            }

            return { fontSize: '20px' };
        },
        roleStyle(text: string): { fontSize: string } {
            return { fontSize: this.getFontSizeFor(text, 18) };
        },
        displayName(person: any): string {
            if (this.showData) {
                return this.givenNameFromPerson(person);
            }

            return 'Navn';
        },
        displayRole(person: any): string {
            if (this.showData) {
                return person?.rolle || person?.role || this.previewRole || 'Rolle';
            }

            return this.previewRole || 'Rolle';
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
    margin: 0 auto;
    position: relative;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
    page-break-after: always;
}

.page.preview {
    transform: scale(0.8);
    transform-origin: top left;
}

.page:last-child {
    page-break-after: auto;
}

.idkort-grid {
    display: grid;
    grid-template-columns: repeat(3, 54mm);
    grid-auto-rows: 85.6mm;
    gap: 6mm;
    padding: 10mm;
    box-sizing: border-box;
}

.idkort-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 1.5mm;
    padding: 8mm 1.5mm 2mm;
    font-family: 'TWKBurns Ultra', Arial, sans-serif;
    line-height: 1.1;
    border: 1px dashed rgba(0, 0, 0, 0.35);
    box-sizing: border-box;
}

.idkort-hole {
    position: absolute;
    top: 2.5mm;
    left: 50%;
    width: 12mm;
    height: 2mm;
    transform: translateX(-50%);
    background: #f5eee4;
    border-radius: 2mm;
}

.logo {
    width: 125%;
    max-width: 70mm;
    height: auto;
    margin-top: 2mm;
}

.name {
    font-size: 24px;
    white-space: normal;
    overflow: hidden;
    text-overflow: clip;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.2;
    height: 14mm;
    align-self: stretch;
    text-align: left;
    padding: 0.4mm 6mm 0 3mm;
    margin-top: 12mm;
}

.role {
    font-size: 18px;
    word-break: break-word;
    position: relative;
    bottom: -0mm;
    margin: 0;
    align-self: stretch;
    text-align: left;
    padding: 0 3mm;
    font-family: 'Inter', Arial, sans-serif;
}
</style>
