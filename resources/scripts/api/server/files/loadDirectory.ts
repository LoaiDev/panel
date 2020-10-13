import http from '@/api/http';
import { rawDataToFileObject } from '@/api/transformers';

export interface FileObject {
    key: string;
    name: string;
    mode: string;
    size: number;
    isFile: boolean;
    isSymlink: boolean;
    mimetype: string;
    createdAt: Date;
    modifiedAt: Date;
    isArchiveType: () => boolean;
    isEditable: () => boolean;
}

export default async (uuid: string, directory?: string): Promise<FileObject[]> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/files/list`, {
        params: { directory },
    });

    return (data.data || []).map(rawDataToFileObject);
};
