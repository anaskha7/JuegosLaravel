import { Link } from '@inertiajs/react';

export default function Pagination({ links = [] }) {
    if (!links.length) {
        return null;
    }

    return (
        <div className="pagination">
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url}
                        className={link.active ? 'active' : ''}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span key={`${link.label}-${index}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                ),
            )}
        </div>
    );
}
