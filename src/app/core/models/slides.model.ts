import { Pictures } from './pictures.model';

export interface Slides {
    id: number;
    name: string;
    description: string;
    link: string;
    folder: string;
    pictures: Pictures;
    mask?: number | string;
    language?: string;
}